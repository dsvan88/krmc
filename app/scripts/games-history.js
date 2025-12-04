
actionHandler.gameHistoryLoad = async function ({target, event}) {
    const content = target.querySelector('.game-history__card');
    if (!content.loaded){
        const result = await request({
            url: '/game/history/'+target.dataset.gameId,
        })
        content.innerHTML = result['html'];
        content.loaded = true;
    }
}