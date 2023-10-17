<section class="section game">
    <div class="game">
			<div class="game__wrapper">
				<div class="game__description">
					<header class="title">
						<div class="game__stage"></div>
					</header>
				</div>
				<table class="game__table">
					<thead>
						<tr>
							<th rowspan="2">#</th>
							<th rowspan="2">Гравець</th>
							<th rowspan="2">Бали</th>
							<th rowspan="2" colspan="<?=$state['daysCount']?>">Вист./Голос</th>
							<th colspan="4">Фолы</th>
						</tr>
						<tr>
							<th>1</th>
							<th>2</th>
							<th>3</th>
							<th>4</th>
						</tr>
					</thead>
					<tbody class="game__table-body">
						<? foreach($state['players'] as $id=>$player):?>
							<tr>
								<td><?=($id+1)?></td>
								<td><?=$player['name']?></td>
								<td><?=$player['points']?></td>
								<? for ($x=0; $x < $state['daysCount']; $x++) :?>
									<td></td>
								<? endfor ?>
								<? for ($x=0; $x < 4; $x++) :?>
									<td></td>
								<? endfor ?>
						<? endforeach ?>
					</tbody>
				</table>
			</div>
			<details class="game__log">
				<summary>Log</summary>
			</details>
		</div>
</section>