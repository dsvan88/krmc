class Noticer {

	noticesPlace = null;
	constructor() {
		this.noticesPlace = document.querySelector('.notices')
		const notices = this.noticesPlace.querySelectorAll('.notice');
		const len = notices.length;
		for (let x = 0; x < len; x++) {
			notices[x].querySelector('.notice__close').addEventListener('click', this.close.bind(this, notices[x]));
		}
	};
	add(notice) {
		if (typeof notice === 'string') {
			notice = { message: notice };
		}
		const noticeBlock = document.createElement('div');
		noticeBlock.classList.add('notice');
		const noticeIcon = document.createElement('div');
		noticeIcon.classList.add('notice__icon', 'fa');
		noticeBlock.append(noticeIcon);
		const noticeMessageBlock = document.createElement('div');
		noticeMessageBlock.classList.add('notice__message');
		noticeMessageBlock.innerHTML = '<p> ' + notice['message'].replace(/\n/g, '</p><p>') + '</p>';

		if (notice['type']) {
			noticeBlock.classList.add(notice['type']);
			const noticeTitle = document.createElement('h3');
			noticeTitle.innerText = notice['type'].toUpperCase() + ':';
			noticeMessageBlock.prepend(noticeTitle);

			noticeIcon.classList.add(notice['type'] === 'error' ? 'fa-times-circle' : 'fa-exclamation-triangle');
		}
		else {
			noticeIcon.classList.add('fa-check-circle-o');
		}

		const noticeCloseBlock = document.createElement('span');
		noticeCloseBlock.className = 'notice__close fa fa-window-close';

		noticeBlock.append(noticeMessageBlock);
		noticeBlock.append(noticeCloseBlock);

		this.noticesPlace.append(noticeBlock);

		noticeCloseBlock.addEventListener('click', this.close.bind(this, noticeBlock))
		if (notice['time']) {
			noticeCloseBlock.timeOut = setTimeout(this.close.bind(this, noticeBlock), notice['time']);
		}
		if (notice["location"]) {
			setTimeout(() => notice["location"] === 'reload' ? window.location.reload() : window.location = notice["location"], notice['time'] ? notice['time'] - 1 : 1000);
		}
	}
	close(notice) {
		notice.style.color = '#00000000';
		notice.style.opacity = '0.0';
		notice.style.height = '0px';
		notice.style.paddingTop = '0px';
		notice.style.paddingBottom = '0px';
		notice.style.marginBottom = '0px';
		if (notice.timeOut) {
			clearTimeout(noticeCloseBlock.timeOut);
		}
		setTimeout(() => notice.remove(), 300);
	}
}