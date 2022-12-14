actionHandler.gameFormSubmit = function (event) {
    event.preventDefault();
	const manager = document.querySelector('input[name="manager"]');
	if (!manager.value.trim())
	{
		alert('Спочатку оберіть гравця серед учасників!')
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
		postAjax({
			url: event.target.action,
			data: formData,
			successFunc: (result) => {
				if (result["error"]) {
					alert(result["message"]);
					return false;
				}
				if (result["message"]) {
					alert(result["message"]);
				}
				if (result["location"]){
					window.location =  result["location"];
				}
			}
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
	// if (target.classList.contains('dummy-player')){
		

	// }
	let fields = document.querySelectorAll('input[name^="player"],input[name^="manager"]');
	if (!target.classList.contains('selected')){
		for(let [index, element] of Object.entries(fields)){
			if (element.value) continue;
			element.value = target.innerText;
			target.classList.add('selected');
			break;
		}
	}
	else {
		for(let [index, element] of Object.entries(fields)){
			if (element.value !== target.innerText) continue;
			element.value = '';
			target.classList.remove('selected');
			break;
		}
	}
}


actionHandler.removePlayer = function (target) {
	console.log(target);
}
