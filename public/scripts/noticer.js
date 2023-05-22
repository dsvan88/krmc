class Noticer {

	noticesPlace = null;
	constructor() {
		const self = this;
		this.noticesPlace = document.querySelector('.notices')
		const notices = this.noticesPlace.querySelectorAll('.notice');
		notices.forEach(notice => {
			notice.querySelector('.notice__close').addEventListener('click', (event) => self.close.call(self, notice));
		});
	};
	add(notice) {
		const  noticeBlock = document.createElement('div');
		noticeBlock.classList.add('notice');
		if (notice['type']) {
			noticeBlock.classList.add(notice['type']);
		}
		const noticeMessageBlock = document.createElement('span');
		noticeMessageBlock.classList.add('notice__message');
		noticeMessageBlock.textContent = notice['message'];


		const noticeCloseBlock = document.createElement('span');
		noticeCloseBlock.className = 'notice__close fa fa-window-close';

		noticeBlock.append(noticeMessageBlock);
		noticeBlock.append(noticeCloseBlock);

		this.noticesPlace.append(noticeBlock);

		const self = this;
		noticeCloseBlock.addEventListener('click', (event) => self.close.call(self, noticeBlock))
	}
	close(notice) {
		notice.style.opacity = 0.0;
		setTimeout(()=> notice.remove(), 500);
	}
}