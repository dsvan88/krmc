class Noticer {

	constructor() {
		const messages = document.querySelectorAll('.notices .notice');
		messages.forEach(notice => {
			notice.querySelector('.notice__close').onclick = this.close(notice);
		});
	};
	close(notice){
		notice.remove();
	}
}