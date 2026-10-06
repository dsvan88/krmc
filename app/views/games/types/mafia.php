
        <div class="game">
			<div class="game__wrapper play">
				<div class="game__description">
					<header class="title">
						<div class="game__stage"></div>
					</header>
					<div class="timer">
						<div class="timer__caption">
						<button class="fa fa-low-vision" data-action-game="display-roles"></button>
						<span class="stopwatch">01:00:00</span>
						<button class="fa fa-gavel" data-action-game="stop-game"></button>
					</div>
						<div class="timer__dashboard">
							<span class="timer__dashboard-item fa fa-history" title="Повернутись" data-timer="undo"></span>
							<span class="timer__dashboard-item fa fa-play" title="Старт" data-timer="start"></span>
							<span class="timer__dashboard-item fa fa-pause" title="Пауза" data-timer="pause"></span>
							<span class="timer__dashboard-item fa fa-undo disabled" title="Відміна" data-timer="reset"></span>
							<span class="timer__dashboard-item fa fa-fast-forward" title="Наступний" data-timer="next"></span>
						</div>
					</div>
				</div>
				<table class="game__table">
					<thead>
						<tr>
							<th rowspan="2">#</th>
							<th rowspan="2">Гравець</th>
							<th rowspan="2">Вист.</th>
							<th colspan="4">Фоли</th>
						</tr>
						<tr>
							<th>1</th>
							<th>2</th>
							<th>3</th>
							<th>4</th>
						</tr>
					</thead>
					<tbody class="game__table-body"></tbody>
				</table>
			</div>
			<div class="courtroom"></div>
			<details class="game__log">
				<summary>Log</summary>
			</details>
		</div>
