class ModalWindow {

	modal = null;
	modalIndex = null;
	modalClose = null;
	commonOverlay = null;
	currentOverlay = null;
	title = null;
	formSubmitHandler = null;
	pauseLayout = null;
	content = null;
	dragged = false;

	get paused() {
		return this.pauseLayout ? true : false;
	}

	constructor({ divId = "modalWindow", html = "", title = "", buttons = [], submit = null } = {}) {
		this.commonOverlay = document.body.querySelector("#overlay");
		if (this.commonOverlay === null) {
			this.commonOverlay = document.createElement("div");
			this.commonOverlay.id = "overlay";
			document.body.append(this.commonOverlay);
			setTimeout(() => this.commonOverlay.style.opacity = 0.3, 100);
		}

		this.prepeare(divId);

		if (html) {
			this.fill({ html, title, buttons, submit });
		}
		this.attachEvents();
	};
	static async create({url= null, data = null, ready = null, submit = null, error = null } = {}){
		
		if (!url)
			throw Error('ModalWindow: url is empty.');

		const modal = new this;

		const response = await request({ url: url, data: data });

		if (response["error"]) {
			error(response) || new Alert({ title: 'Error!', text: data["message"] });
			return modal.close();
		}
		
		if (!response['modal'])
			throw Error('ModalWindow: response isn’t a modal.');
		
		if (!response['html'])
			throw Error('ModalWindow: response.html is empty.');

		if (data["jsFile"]) {
			await addScriptFile(data["jsFile"]);
		};

		if (data["cssFile"]) {
			await addCssFile(data["cssFile"]);
		};

		modal.fill(response)

		if (submit && modal.content) {
			modal.formSubmitHandler = submit;
			let form = modal.content.querySelector('form');
			if (!form) {
				form = document.createElement('form');
				form.action = "/";
				form.method = "POST";
				form.innerHTML = modal.content.innerHTML;
				modal.content.innerHTML = form.outerHTML;
				form = modal.content.querySelector('form');
			}
			form.addEventListener('submit', e => modal.submit(e))
		}		
		
		if (ready) {
			await ready(modal, response);
		}

		return modal;
	}
	fill({ html = "", title = "", buttons = [], submit = null }) {
		if (html) {
			this.content.innerHTML = html;
		}

		if (title) {
			if (/<\w+?>/.test(title)) {
				this.title.innerHTML = title;
			} else {
				this.title.innerText = title;
			}
		}
		if (buttons.length !== 0) {
			const modalContainer = this.content || this.modal.querySelector('.modal__container');
			const modalButtons = document.createElement('div');
			modalButtons.className = 'modal__buttons';
			buttons.forEach(button => {
				const element = document.createElement('button');
				element.innerText = button.text;
				element.className = button.className;
				element.type = button.type ? button.type : 'button';
				modalButtons.append(element);
			})
			modalContainer.append(modalButtons)
			this.content = modalContainer;
		}

		return this.content;
	};
	clear() {
		this.modal.querySelector('.modal__container').innerHTML = '';
	};
	prepeare(divId = "modalWindow") {
		let modalHeader = document.createElement("div");
		modalHeader.className = "modal__header";

		this.title = document.createElement("h3");
		this.title.className = 'modal__title';
		this.title.innerHTML = 'Завантаження...';

		this.modalClose = document.createElement("i");
		this.modalClose.className = "fa fa-window-close modal__close";
		modalHeader.append(this.title);
		modalHeader.append(this.modalClose);

		this.modal = document.createElement("div");
		this.modal.className = "modal";
		this.modal.append(modalHeader);

		this.content = document.createElement("div");
		this.content.classList.add('modal__container');

		const modalRow = document.createElement("div");
		modalRow.classList.add('modal__buttons');
		const icon = document.createElement("i");
		icon.classList.add('fa', 'fa-cog', 'fa-spin', 'fa-3x', 'fa-fw')
		const iconLabel = document.createElement("span");
		iconLabel.classList.add('sr-only');
		iconLabel.innerText = 'Завантаження...';
		modalRow.append(icon, iconLabel);
		this.content.append(modalRow);
		this.modal.append(this.content);

		this.currentOverlay = document.createElement("div");
		this.currentOverlay.className = "modal__overlay modal__close";
		this.currentOverlay.id = divId;
		this.currentOverlay.append(this.modal);

		document.body.append(this.currentOverlay);

		this.currentOverlay.addEventListener("click", (event) => this.close(event));

		this.popUp();
	};
	popUp() {

		this.modalIndex = document.body.querySelectorAll(".modal").length;

		this.currentOverlay.style.zIndex = 7 + this.modalIndex;
		this.modal.style.opacity = 0;
		this.modal.style.display = 'block';
		setTimeout(() => {
			this.modal.style.opacity = 1
			this.modal.style.transform = 'translateY(-2%)';
		}, 100);

	};
	pause() {
		this.pauseLayout = document.createElement('div');
		this.pauseLayout.classList.add('modal__pause');
		const pauseIcon = document.createElement('i');
		pauseIcon.classList.add('fa', 'fa-cog', 'fa-spin', 'fa-3x', 'fa-fw');
		this.pauseLayout.append(pauseIcon);
		this.content.append(this.pauseLayout);
	}
	unpause() {
		this.pauseLayout.remove();
	}
	close(event) {
		if (event && event.target) {
			if (!event.target.classList.contains("modal__close"))
				return;

			if (this.currentOverlay === event.target && !confirm('Ви впевнені, що бажаєте закрити поточне вікно?'))
				return;
		}
		if (this.modalIndex === 1) {
			this.commonOverlay.style.opacity = 0;
			setTimeout(() => this.commonOverlay.remove(), 300);
		}

		this.modal.style.opacity = 0;
		this.modal.style.transform = 'translateY(2%)';
		setTimeout(() => {
			this.modal.remove()
			this.currentOverlay.remove()
		}, 300);
	}
	submit(event) {
		event.preventDefault();
		this.pause();
		const formData = new FormData(event.target);
		this.formSubmitHandler(event, formData, this);
	}

	attachEvents() {
		this.modal.addEventListener("click", this.clickFunc.bind(this))

		this.modal.ondragstart = () => false;

		this.title.addEventListener('mousedown', this.dragStart.bind(this));

		this.modal.addEventListener('touchstart', this.dragStart.bind(this));
	}
	clickFunc(e){
		if (e.target.classList.contains('modal__close')) this.close();
	}
	dragStart(event) {

		if (this.dragged) return;
		this.dragged = true;

		const clientX = event.clientX || event.targetTouches[0].clientX;
		const clientY = event.clientY || event.targetTouches[0].clientY;

		this.shiftX = clientX - this.modal.getBoundingClientRect().left;
		this.shiftY = clientY - this.modal.getBoundingClientRect().top;

		this.dragnDrop(event);
	}
	dragnDrop(event) {

		const self = this;

		self.modal.style.position = 'absolute';
		self.modal.style.zIndex = 1000;
		self.modal.style.margin = 0;

		document.body.append(self.modal);

		const pageX = event.pageX || event.targetTouches[0].pageX;
		const pageY = event.pageY || event.targetTouches[0].pageY;

		self.moveAt(pageX, pageY);

		document.context = self;
		document.addEventListener('mousemove', self.onMouseMove);
		document.addEventListener('touchmove', self.onMouseMove);

		self.modal.onmouseup = (event) => this.moveEnd(event);
		self.modal.ontouchend = (event) => this.moveEnd(event);
	}
	moveEnd() {
		document.removeEventListener('mousemove', this.onMouseMove);
		document.removeEventListener('touchmove', this.onMouseMove);
		this.dragged = false;
		document.context = null;
		this.modal.ontouchend = null;
		this.modal.style.zIndex = 8 + this.modalIndex;;
	}
	moveAt(pageX, pageY) {
		this.modal.style.left = pageX - this.shiftX + 'px';
		this.modal.style.top = pageY - this.shiftY + 'px';
	}
	onMouseMove(event) {
		const self = document.context;

		if (!self) return false;

		const pageX = event.pageX || event.targetTouches[0].pageX;
		const pageY = event.pageY || event.targetTouches[0].pageY;

		self.moveAt(pageX, pageY);
	}
}