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
		const noticeMessageBlock = document.createElement('div');
		noticeMessageBlock.classList.add('notice__message');
		noticeMessageBlock.innerHTML = '<p> ' + notice['message'].replace(/\n/g, '</p><p>') + '</p>';
		
		if (notice['type']) {
			noticeBlock.classList.add(notice['type']);
			const noticeTitle = document.createElement('h3');
			noticeTitle.innerText = notice['type'].toUpperCase() + ':';
			noticeMessageBlock.prepend(noticeTitle);
		}

		const noticeCloseBlock = document.createElement('span');
		noticeCloseBlock.className = 'notice__close fa fa-window-close';

		noticeBlock.append(noticeMessageBlock);
		noticeBlock.append(noticeCloseBlock);

		this.noticesPlace.append(noticeBlock);

		const self = this;
		noticeCloseBlock.addEventListener('click', (event) => self.close.call(self, noticeBlock))
	}
	close(notice) {
		notice.style.opacity = '0.0';
		notice.style.height = '0px';
		notice.style.paddingTop = '0px';
		notice.style.paddingBottom = '0px';
		setTimeout(()=> notice.remove(), 300);
	}
}