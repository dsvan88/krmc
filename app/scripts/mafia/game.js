const Mafia = new MafiaEngine({
    gameTable: ".game__table .game__table-body",
});
const Timer = new GameTimer({
        gameEngine: Mafia,
});
// Mafia.gameTable.addEventListener('ready', (event) => {
//     Timer = new GameTimer({
//         gameEngine: Mafia,
//     });
// });


actionHandler.gamePutHim = function (target) {
    const playerRow = target.closest('tr');
    Mafia.putPlayer(parseInt(playerRow.dataset.playerId))
}
actionHandler.gameFoulsMenu = function (target) {
    const playerRow = target.closest('tr');
    Mafia.applyPlayerFoul(parseInt(playerRow.dataset.playerId))
}
// actionHandler.gameFouls = function (target) {
//     let playerRow = target.closest('tr');
//     Mafia.playerFouls(parseInt(playerRow.dataset.playerId), target.dataset.foul)
// }
