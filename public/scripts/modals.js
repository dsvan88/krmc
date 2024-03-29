class ModalWindow {

	modal = null;
	modalIndex = null;
	modalClose = null;
	commonOverlay = null;
	currentOverlay = null;
	title = null;
	formSubmitHandler = null;
	pauseLayout = null;
	// context = null;
	content = null;
	dragged = false;

	get paused() {
		return this.pauseLayout ? true : false;
	}

	constructor({ divId = "modalWindow", html = "", title = "", buttons = [], submit = null, context = null } = {}) {
		this.commonOverlay = document.body.querySelector("#overlay");
		if (this.commonOverlay === null) {
			this.commonOverlay = document.createElement("div");
			this.commonOverlay.id = "overlay";
			document.body.append(this.commonOverlay);
			setTimeout(() => this.commonOverlay.style.opacity = 0.3, 100);
		}

		this.prepeare(divId);

		if (html) {
			this.fill({ html, title, buttons, submit, context });
		}
		this.attachEvents();
	};
	fill({ html = "", title = "", buttons = [], submit = null, context = null }) {
		let modalContainer = null;
		if (html) {
			this.content.innerHTML = html;
		}
		
		if (title){
			if (/<\w+?>/.test(title)){
				this.title.innerHTML = title;
			} else {
				this.title.innerText = title;
			}
		}
		if (buttons.length !== 0) {
			modalContainer = this.content || this.modal.querySelector('.modal__container');
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
		
		if (submit && this.content) {
			this.formSubmitHandler = submit;
			this.context = context;
			let form = this.content.querySelector('form');
			if (!form) {
				form = document.createElement('form');
				form.action = "/";
				form.method = "POST";
				form.innerHTML = this.content.innerHTML;
				this.content.innerHTML = form.outerHTML;
				form = this.content.querySelector('form');
			}
			form.addEventListener('submit', event => this.submit(event))
		}
		this.attachEvents();

		// this.content.dispatchEvent(new Event('load'));

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

		this.currentOverlay.addEventListener("click",  (event) => this.close(event));

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
		if (event && event.target){
			if (!event.target.classList.contains("modal__close"))
				return;

			if (this.currentOverlay === event.target && !confirm('Ви впевнені, що бажаєте закрити поточне вікно?'))
				return;
		}
		if (this.modalIndex === 1) {
			this.commonOverlay.style.opacity = 0;
			setTimeout(() => this.commonOverlay.remove(), 300);
		}
		const _self = this;

		this.modal.style.opacity = 0;
		this.modal.style.transform = 'translateY(2%)';
		setTimeout(() => this.currentOverlay.remove(), 300);
	}
	submit(event) {
		event.preventDefault();
		this.pause();
		let formData = new FormData(event.target);
		if (this.context) {
			this.formSubmitHandler.call(this.context, event, formData, this);
		}
		else{
			this.formSubmitHandler(event, formData, this);
		}
	}
	attachEvents() {
        const self = this;

		self.currentOverlay.querySelectorAll('.modal__close').forEach(element => element.addEventListener("click",  (event) => self.close(event)) );

        // self.modal.ondragstart = () => false;

        // self.title.addEventListener('mousedown', (event) => self.dragStart.call(self, event));

        // self.modal.addEventListener('touchstart', (event) => self.dragStart.call(self, event));
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
    moveEnd(event) {
		// console.log(event);
        document.removeEventListener(event.type, this.onMouseMove);
        this.dragged = false;
        this.modal.ontouchend = null;
        document.context = null;
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