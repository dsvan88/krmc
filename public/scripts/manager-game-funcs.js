actionHandler.gameFormSubmit = function (event) {
	event.preventDefault();
	const self = actionHandler;
	const manager = document.querySelector('input[name="manager"]');
	if (!manager.value.trim())
	{
		alert('Спочатку оберіть ведучого серед учасників!')
		return false;
	}
	let role=0, roles=[0, 0, 0, 0, 0];
	const players = document.querySelectorAll('input[name^="player"]');
	for(const [index, player] of Object.entries(players)){
		if (!player.value){
			alert(`Когось не вистачає! (Відсутній гравець №${index+1})`);
			return false;
		}
		role = document.querySelector(`select[name="role[${index}]"]`).value;
		roles[role]++;
	}
	if (roles[1] !== 2 || roles[2] !== 1 || roles[4] !==1){
		alert('Невірно розподілені ролі!\n(Ролей всього: Гравців Мафії - 2, Дон - 1, Шеріф - 1, Мирні - 6)');
		return false;
	}

	const formData = new FormData(event.target);
	request({
		url: event.target.action,
		data: formData,
		success: result => self.commonResponse.call(self, result),
	});
	return true;
		
}

let gameForm = document.body.querySelector('.game-form');
if (gameForm) {
    gameForm.onsubmit = actionHandler.gameFormSubmit;
}


actionHandler.togglePlayer = function (target) {
	if (target.classList.contains('dummy-player')){
		const modal = new ModalWindow();
		request({
			url: 'account/dummy/rename/form',
			success: response => modal.fill({html:response['html'], title: response['title']}),
		});
	}
	const fields = document.querySelectorAll('input[name^="player"],input[name^="manager"]');
	const current = new Map();
	let firstEmpty = null;
	fields.forEach(field => {
		if (field.value !== '')
			return current.set(field.value, field);
		if (firstEmpty)
			return false;
		firstEmpty = field;		
	});

	if (current.has(target.innerText)){
		current.get(target.innerText).value = '';
		target.classList.remove('selected');
		return true;
	}

	if (!firstEmpty) return false;

	target.classList.add('selected');
	firstEmpty.value = target.innerText;
	return true;
}

actionHandler.checkPlayer = function (event) {
	const self = this;
	const target = event.target;
	const fields = document.querySelectorAll('input[name^="player"],input[name^="manager"]');
	for(const field of fields){
		if (field.value === '' || field === target) continue;
		if (field.value === target.value) {
			if (confirm('Already in the list!\nRemove previous?'))
				field.value = '';
			else 
				target.value = '';
			return false;
		}
	};
	const playerButtons = document.querySelectorAll('*[data-action-click="toggle-player"]');
	for (const button of playerButtons){
		if (button.innerText === target.value){
			self.resetSelectedPoolUnits();
			return true;
		}
	}

	if (!confirm(`New player "${target.value}"?\nAre you sure?`)){
		target.value = '';
		return false;
	}
	
	this.addParticipant(target.value, target);
}

actionHandler.resetSelectedPoolUnits = function () {
	const fields = document.querySelectorAll('input[name^="player"],input[name^="manager"]');
	const players = new Set();
	for(const field of fields){
		if (field.value === '') continue;
		players.add(field.value);
	};
	const playerButtons = document.querySelectorAll('*[data-action-click="toggle-player"]');
	for (const button of playerButtons){
		if (players.has(button.innerText)){
			button.classList.add('selected');
			continue;
		}
		button.classList.remove('selected');
	}
}

actionHandler.removeParticipant = function (target) {
	const self = this;
	const button = target.closest('.game-form__pool-unit');
	const name = button.querySelector('.game-form__pool-name').innerText;
	const answer = prompt(`Are you sure?\nIt will remove ${name}'s progress for today?\nEnter 'Y' or 'Yes' below:`, 'No') || 'No';
	
	if (answer.toLowerCase().trim()[0] !== 'y') return false;

	const fields = document.querySelectorAll('input[name^="player"],input[name^="manager"]');
	for(const field of fields){
		if (field.value === '' || field.value !== name) continue;
		field.value = '';
		break;
	};
	button.remove();

	const formData = new FormData();
	formData.append('name', name);
	request({
		url: 'game/remove-participant',
		data: formData,
		success: (result) => {
			self.commonResponse.call(self, result);
		},
	});

}

actionHandler.addParticipantFormSubmit = function (event, modal) {
	event.preventDefault();
	const name = event.target.querySelector('input[name="name"]').value;
	actionHandler.addParticipant(name);
	modal.close()
}

actionHandler.addParticipant = function (name, target = null) {
	const self = this;
	const formData = new FormData();
	formData.append('name', name);
	request({
		url: 'game/add-participant',
		data: formData,
		success: (result) => {
			self.commonResponse.call(self, result);
			if (target){
				if (result['notice'] && result['notice']['error']){
					target.value = '';
					return false;
				}
				target.value = result['name'];
			}

			const poolUniExpample = document.querySelector('span.game-form__pool-unit');
			if (poolUniExpample){
				const poolUnitNew = poolUniExpample.cloneNode(true);
				poolUnitNew.querySelector('span.game-form__pool-name').innerText = result['name'];
				const parentElement = poolUniExpample.closest('div.game-form__pool');
				parentElement.insertBefore(poolUnitNew, document.querySelector('span.game-form__pool-unit.add'));
				
			}
			self.resetSelectedPoolUnits();
		},
	});
}
