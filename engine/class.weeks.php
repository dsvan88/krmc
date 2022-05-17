<?
require_once $_SERVER['DOCUMENT_ROOT'] . '/engine/class.action.php';

class Weeks
{
	private $action;
	function __construct()
	{
		$this->action = $GLOBALS['CommonActionObject'];
	}
	// Получить настройки недели по времени
	public function getDataByTime($time = 0)
	{
		if ($time === 0)
			$time = $_SERVER['REQUEST_TIME'];
		$result = $this->action->getAssocArray($this->action->prepQuery('SELECT id,data,start,finish FROM ' . SQL_TBLWEEKS . ' WHERE start < :time AND finish > :time LIMIT 1', ['time' => $time]));
		if ($result !== []) {
			$result = $result[0];
			$result['data'] = json_decode($result['data'], true);
			return $result;
		}
		return false;
	}
	// Получить настройки недели по id недели
	public function getDataById($id)
	{
		$result = $this->action->getAssocArray($this->action->prepQuery('SELECT id,data,start FROM ' . SQL_TBLWEEKS . ' WHERE id = ? LIMIT 1', [$id]));
		if ($result !== []) {
			$result = $result[0];
			$result['data'] = json_decode($result['data'], true);
			return $result;
		}
		return false;
	}
	// Получить настройки последней зарегистрированной недели
	public function getLastWeekData()
	{
		$result = $this->action->getAssocArray($this->action->query('SELECT id,data FROM ' . SQL_TBLWEEKS . ' ORDER BY id DESC LIMIT 1'));
		if ($result !== []) {
			$result = $result[0];
			$result['data'] = json_decode($result['data'], true);
			return $result;
		}
		return false;
	}
	// Получить настройки будущих зарегистрированных недель
	public function getNearWeeksDataByTime()
	{
		$time = $_SERVER['REQUEST_TIME'];
		$result = $this->action->getAssocArray($this->action->prepQuery('SELECT id,data,start,finish FROM ' . SQL_TBLWEEKS . ' WHERE finish > ? ORDER BY id ASC', [$time]));
		if ($result !== []) {
			for ($i = 0; $i < count($result); $i++) {
				$result[$i]['data'] = json_decode($result[$i]['data'], true);
			}
			return $result;
		}
		return false;
	}
	public function getDataDefault($sunday = 0)
	{
		if ($sunday === 0) {
			$result = $this->getLastWeekData();
		} else {
			$time = $sunday - TIMESTAMP_WEEK;
			$result = $this->getDataByTime($time);
		}

		if ($result) {
			$weekData = $result;
			$weekData['id'] = 0;
			if (is_string($weekData['data'])) {
				$weekData['data'] = json_decode($weekData['data'], true);
			}
			for ($i = 0; $i < 7; $i++) {
				$weekData['data'][$i]['participants'] = [];
			}
		} else {
			$weekData = [
				'id' => 0,
				'data' => []
			];
			for ($i = 0; $i < 7; $i++) {
				$weekData['data'][] = $this->getDayDataDefault();
			}
		}
		return $weekData;
	}
	public function getDayDataDefault()
	{
		return [
			'game' => 'mafia',
			'mods' => [],
			'time' => '18:00',
			'status' => '',
			'participants' => []
		];
	}
	public function daySetApproved($data)
	{
		$weekId = $data['weekId'];
		unset($data['weekId']);
		$dayId = $data['dayId'];
		unset($data['dayId']);

		$weekData = false;

		if ($weekId > 0) {
			$weekData = $this->getDataById($weekId);
			if ($weekData !== false)
				$weekId = $weekData['id'];
		}

		if ($weekId > 0) {
			$weekData['data'][$dayId] = $data;
			$weekData['data'][$dayId]['status'] = '';
			$result = $this->action->rowUpdate(['data' => json_encode($weekData['data'])], ['id' => $weekId], SQL_TBLWEEKS);
			if ($result)
				return $weekId;
			return false;
		} else {
			$sunday = strtotime('next sunday 23:59:59');
			$monday = strtotime('last monday 00:00:01', $sunday);
			if ($weekId === -1) {
				$sunday += TIMESTAMP_WEEK;
				$monday += TIMESTAMP_WEEK;
			}
			$weekData = [
				'start' => $monday,
				'finish' => $sunday,
				'data' => json_encode([$dayId => $data], JSON_UNESCAPED_UNICODE)
			];

			$result = $this->action->rowInsert($weekData, SQL_TBLWEEKS);
			if ($result !== 0)
				return $result;
			return false;
		}
	}
	public function dayUserRegistrationByTelegram($data)
	{
		$weekData = false;
		if ($data['currentDay'] > $data['dayNum']) {
			$weeksData = $this->getNearWeeksDataByTime();
			foreach ($weeksData as $index => $tempWeekData) {
				$dayTime = $tempWeekData['start'] + TIMESTAMP_DAY * $data['dayNum'];
				if ($dayTime > $_SERVER['REQUEST_TIME']) {
					$weekData = $tempWeekData;
					break;
				}
			}
			if (!$weekData) {
				$weekData['id'] = -1;
			}
		}

		if (!$weekData)
			$weekData = $this->getDataByTime();

		if ($weekData['id'] === -1) {
			$checkId = $this->getCurrentId();
			if (!$checkId)
				return ['result' => false, 'message' => 'Прежде, чем планировать новую неделю - оформите что-то на этой!'];
		}

		$id = -1;
		if (!isset($weekData['data'][$data['dayNum']])) {
			if (!in_array($data['userStatus'], ['admin', 'manager']))
				return ['result' => false, 'message' => 'Игр на указанный день, пока не запланировано!'];
			else {
				$defaultData = $this->getDataDefault();
				$weekData['data'][$data['dayNum']] = $defaultData['data'][$data['dayNum']];

				if (!isset($weekData['data'][$data['dayNum']]['game']))
					$weekData['data'][$data['dayNum']] = $this->getDayDataDefault();

				if ($data['arrive'] !== '')
					$weekData['data'][$data['dayNum']]['time'] = $data['arrive'];
				$data['arrive'] = '';
			}
		} else {
			if ($weekData['data'][$data['dayNum']]['status'] === 'recalled') {
				if (!in_array($data['userStatus'], ['admin', 'manager']))
					return ['result' => false, 'message' => 'Игр на указанный день, пока не запланировано!'];
				else
					$weekData['data'][$data['dayNum']]['status'] = '';
			}
			foreach ($weekData['data'][$data['dayNum']]['participants'] as $index => $userData) {
				if ($userData['id'] === $data['userId']) {
					$id = $index;
					break;
				}
			}
		}

		if ($id !== -1)
			return ['result' => false, 'message' => 'Вы уже зарегистрированны на этот день!'];

		$newData = $weekData['data'][$data['dayNum']];
		$newData['weekId'] = $weekData['id'];
		$newData['dayId'] = $data['dayNum'];

		$freeSlot = -1;
		while (isset($newData['participants'][++$freeSlot])) {
		}

		$newData['participants'][$freeSlot] = [
			'id'	=>	$data['userId'],
			'name'	=>	$data['userName'],
			'arrive'	=>	$data['arrive'],
			'duration'	=> 	(int) $data['duration']
		];

		$result = $this->daySetApproved($newData);

		if (!$result) {
			return ['result' => false, 'message' => json_encode($newData, JSON_UNESCAPED_UNICODE)];
		}
		/* 
		$dayNames = ['в <b>Понедельник</b>', 'во <b>Вторник</b>', 'в <b>Среду</b>', 'в <b>Четверг</b>', 'в <b>Пятницу</b>', 'в <b>Субботу</b>', 'в <b>Воскресенье</b>'];
		$gameNames = [
			'mafia' => 'Мафия 🎭',
			'poker' => 'Покер ♦️',
			'board' => 'Настолки 🎲',
			'cash' => 'Кеш-покер 🃏'
		]; */
		$weekData['data'][$data['dayNum']] = $newData;
		return ['result' => true, 'message' => $this->getDayFullDescription($weekData, $data['dayNum'])];
	}
	public function getDayFullDescription($weekData, $day)
	{
		$format = "d.m.Y {$weekData['data'][$day]['time']}";
		$dayDate = strtotime(date($format, $weekData['start'] + TIMESTAMP_DAY * $day));

		if ($_SERVER['REQUEST_TIME'] > $dayDate + DATE_MARGE || $weekData['data'][$day]['status'] === 'recalled') {
			return '';
		}

		$date = str_replace(
			['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
			['<b>Понедельник</b>', '<b>Вторник</b>', '<b>Среда</b>', '<b>Четверг</b>', '<b>Пятница</b>', '<b>Суббота</b>', '<b>Воскресенье</b>'],
			date('d.m.Y (l) H:i', $dayDate)
		);
		$gameNames = [
			'mafia' => 'Мафия 🎭',
			'poker' => 'Покер ♦️',
			'board' => 'Настолки 🎲',
			'cash' => 'Кеш-покер 🃏'
		];

		$durations = [
			'',
			'1-2',
			'2-3',
			'3-4'
		];

		$costs = [
			'mafia' => 90,
			'poker' => 70,
			'board' => 50,
			'cash' => 400
		];
		$result = '';

		$result .= "$date - {$gameNames[$weekData['data'][$day]['game']]}\r\n";

		if (in_array('fans', $weekData['data'][$day]['mods'], true))
			$result .= "*<b>ФАНОВАЯ</b>! Хорошо проведите время и повеселитесь!\r\n";
		if (in_array('tournament', $weekData['data'][$day]['mods'], true))
			$result .= "<b>ТУРНИР</b>! Станьте чемпионом в равной борьбе!\r\n";
		if (isset($weekData['data'][$day]['prim']) && $weekData['data'][$day]['prim'] !== '')
			$result .= "<u>{$weekData['data'][$day]['prim']}</u>\r\n";

		$result .= "\r\n";

		for ($x = 0; $x < count($weekData['data'][$day]['participants']); $x++) {
			$modsData = '';
			if ($weekData['data'][$day]['participants'][$x]['arrive'] !== '' && $weekData['data'][$day]['participants'][$x]['arrive'] !== $weekData['data'][$day]['time']) {
				$modsData .= $weekData['data'][$day]['participants'][$x]['arrive'];
				if ($weekData['data'][$day]['participants'][$x]['duration'] != 0) {
					$modsData .= ', ';
				}
			}
			if ($weekData['data'][$day]['participants'][$x]['duration'] != 0) {
				$modsData .= "на {$durations[$weekData['data'][$day]['participants'][$x]['duration']]} игры";
			}
			if ($modsData !== '')
				$modsData = " (<i>$modsData</i>)";
			$result .= ($x + 1) . ". <b>{$weekData['data'][$day]['participants'][$x]['name']}</b>{$modsData}\r\n";
		}
		return $result;
	}
	public function dayUserUnregistrationByTelegram($data)
	{
		$weekData = false;
		if ($data['currentDay'] > $data['dayNum']) {
			$weeksData = $this->getNearWeeksDataByTime();
			$id = -1;
			foreach ($weeksData as $index => $tempWeekData) {
				$dayTime = $tempWeekData['start'] + TIMESTAMP_DAY * $data['dayNum'];
				if ($dayTime > $_SERVER['REQUEST_TIME']) {
					$weekData = $tempWeekData;
					break;
				}
			}
		}

		if (!$weekData)
			$weekData = $this->getDataByTime();

		if (!isset($weekData['data'][$data['dayNum']])) {
			return ['result' => false, 'message' => 'Игр на указанный день, пока не запланировано!'];
		}

		$id = -1;
		foreach ($weekData['data'][$data['dayNum']]['participants'] as $index => $userData) {
			if ($userData['id'] === $data['userId']) {
				$id = $index;
				unset($weekData['data'][$data['dayNum']]['participants'][$index]);
				break;
			}
		}
		$weekData['data'][$data['dayNum']]['participants'] = array_values($weekData['data'][$data['dayNum']]['participants']);

		if ($id === -1)
			return ['result' => false, 'message' => 'Вы не были записаны на этот день!'];

		$newData = $weekData['data'][$data['dayNum']];
		$newData['weekId'] = $weekData['id'];
		$newData['dayId'] = $data['dayNum'];

		$result = $this->daySetApproved($newData);

		if (!$result) {
			return ['result' => false, 'message' => json_encode($newData, JSON_UNESCAPED_UNICODE)];
		}
		$dayNames = ['в <b>Понедельник</b>', 'во <b>Вторник</b>', 'в <b>Среду</b>', 'в <b>Четверг</b>', 'в <b>Пятницу</b>', 'в <b>Субботу</b>', 'в <b>Воскресенье</b>'];
		$gameNames = [
			'mafia' => 'Мафия 🎭',
			'poker' => 'Покер ♦️',
			'board' => 'Настолки 🎲',
			'cash' => 'Кеш-покер 🃏'
		];
		return ['result' => true, 'message' => "Вы успешно отписались от игры <b>'{$gameNames[$weekData['data'][$data['dayNum']]['game']]}'</b> {$dayNames[$data['dayNum']]}."];
	}
	public function getCount()
	{
		return $this->action->getColumn($this->action->query('SELECT count(id) FROM ' . SQL_TBLWEEKS));
	}
	public function getIds()
	{
		return $this->action->getRawArray($this->action->query('SELECT id FROM ' . SQL_TBLWEEKS . ' ORDER BY id'));
	}
	public function checkByTime($time = 0)
	{
		if ($time === 0)
			$time = $_SERVER['REQUEST_TIME'];
		$result = $this->action->getColumn($this->action->prepQuery('SELECT id FROM ' . SQL_TBLWEEKS . ' WHERE start < :time AND finish > :time LIMIT 1', ['time' => $time]));
		return $result > 0 ? true : false;
	}
	public function getCurrentId()
	{
		$time = $_SERVER['REQUEST_TIME'];
		$result = $this->action->getColumn($this->action->prepQuery('SELECT id FROM ' . SQL_TBLWEEKS . ' WHERE start < :time AND finish > :time LIMIT 1', ['time' => $time]));
		return $result;
	}
	public function getAllWeeksData()
	{
		$result = $this->action->getAssocArray($this->action->query('SELECT id,data,start,finish FROM ' . SQL_TBLWEEKS));
		return $result;
	}
	public function autoloadWeekData($weekId)
	{
		$cId = $this->getCurrentId();
		$wIds = $this->getIds();
		$wIdsInList = -1;
		if ($cId)
			$wIdsInList = array_search($cId, $wIds);
		else
			$cId = 0;

		if ($weekId > 0) {
			return [$cId, $wIds, $wIdsInList, $this->getDataById($weekId)];
		}
		if ($weekId === 0) {
			if ($cId) {
				return [$cId, $wIds, $wIdsInList, $this->getDataById($cId)];
			}
			return [$cId, $wIds, $wIdsInList, $this->getDataDefault()];
		}
		if ($weekId === -1) {
			return [$cId, $wIds, $wIdsInList, $this->getDataDefault()];
		}
	}
	public function dayRecall($data)
	{
		$weekData = $this->getDataById($data['weekId']);
		if (!isset($weekData['data'][$data['dayNum']]) || $weekData['data'][$data['dayNum']]['status'] === 'recalled') {
			return false;
		}
		$weekData['data'][$data['dayNum']]['status'] = 'recalled';
		return $this->action->rowUpdate(['data' => json_encode($weekData['data'])], ['id' => $data['weekId']], SQL_TBLWEEKS);
	}
	public function dayRecallByTelegram($data)
	{
		$currentWeekId = $this->getCurrentId();
		if ($data['dayNum'] >= $data['currentDay']) {
			$result = $this->dayRecall(['weekId' => $currentWeekId, 'dayNum' => $data['dayNum']]);
			if (!$result)
				return 'Не знайдено відповідного дня, серед запланованих.';
			return 'Успішно відмінено';
		} else {
			$weeksData = $this->getNearWeeksDataByTime();
			return json_encode($weeksData, JSON_UNESCAPED_UNICODE);
			$weekId = -1;
			if (count($weeksData) < 2)
				return 'Не знайдено відповідного дня, серед запланованих.';

			for ($i = 1; $i < count($weeksData); $i++) {
				if (isset($weeksData[$i]['data'][$data['dayNum']])) {
					$weekId = $weeksData[$i]['id'];
				}
			}

			if ($weekId === -1) {
				return 'Не знайдено відповідного дня, серед запланованих.';
			}

			$result = $this->dayRecall(['weekId' => $weekId, 'dayNum' => $data['dayNum']]);
			if (!$result)
				return 'Не знайдено відповідного дня, серед запланованих.';
			return 'Успішно відмінено';
		}
	}
}
