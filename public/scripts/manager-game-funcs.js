actionHandler.gameFormSubmit = function (event) {
    event.preventDefault();
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
	if (roles[1]===2 && roles[2]===1 && roles[4]===1)
	{
		const formData = new FormData(event.target);
		request({
			url: event.target.action,
			data: formData,
			success: actionHandler.commonResponse,
		});
	}
	else
	{
		alert('Невірно розподілені ролі!\n(Ролей всього: Гравців Мафії - 2, Дон - 1, Шеріф - 1, Мирні - 6');
	}
	return false;
}

let gameForm = document.body.querySelector('.game-form');
if (gameForm) {
    gameForm.onsubmit = actionHandler.gameFormSubmit;
}


actionHandler.togglePlayer = function (target) {
	if (target.classList.contains('dummy-player')){
		const modal = new ModalWindow();
		request({
			url: 'account/rename/form',
			success: response => modal.fillModalContent({html:response['html'], title: response['title']}),
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


actionHandler.removePlayer = function (target) {
	console.log(target);
}
