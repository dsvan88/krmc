class ModalWindow {

	modal = null;
	modalIndex = null;
	commonOverlay = null;
	currentOverlay = null;
	title = null;
	formSubmitHandler = null;
	context = null;
	content = null;
	dragged = false;

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
			this.content = this.content || this.modal.querySelector('.modal__container');
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

		let modalClose = document.createElement("i");
		modalClose.className = "fa fa-window-close modal__close";
		modalHeader.append(this.title);
		modalHeader.append(modalClose);

		this.modal = document.createElement("div");
		this.modal.className = "modal";
		this.modal.append(modalHeader);
		/* ВОТ ГДЕ КОСЯК*/
		this.modal.innerHTML += `
			<div class="modal__container">
				<div class="modal__buttons">
					<i class="fa fa-cog fa-spin fa-3x fa-fw" ></i>
					<span class="sr-only">Завантаження...</span>
				</div>
			</div>`;

		this.currentOverlay = document.createElement("div");
		this.currentOverlay.className = "modal__overlay modal__close";
		this.currentOverlay.id = divId;
		this.currentOverlay.append(this.modal);
		this.content = this.modal.querySelector('.modal__container');

		document.body.append(this.currentOverlay);
		const _self = this;
		this.currentOverlay.addEventListener("click",  (event) => _self.close(event));

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
		this.currentOverlay.removeEventListener("click", (event) => _self.close(event));

		this.modal.style.opacity = 0;
		this.modal.style.transform = 'translateY(2%)';
		setTimeout(() => this.currentOverlay.remove(), 300);
	}
	submit(event) {
		let formData = new FormData(event.target);
		if (this.context) {
			this.formSubmitHandler.call(this.context, formData, event);
		}
		else{
			this.formSubmitHandler(formData, event);
		}
		this.close();
	}
	attachEvents() {
        const self = this;

        self.modal.ondragstart = () => false;

        self.title.addEventListener('mousedown', (event) => self.dragStart.call(self, event));

        self.modal.addEventListener('touchstart', (event) => self.dragStart.call(self, event));
    }
	dragStart(event) {
		console.log(event)
        if (this.dragged) return;
        this.dragged = true;

        const clientX = event.clientX || event.targetTouches[0].clientX;
        const clientY = event.clientY || event.targetTouches[0].clientY;

        this.shiftX = clientX - this.dialog.getBoundingClientRect().left;
        this.shiftY = clientY - this.dialog.getBoundingClientRect().top;

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

        self.modal.onmouseup = (event) => this.moveEnd(event, 'mousemove');
        self.modal.ontouchend = (event) => this.moveEnd(event, 'touchmove');

    }
    moveEnd(event, eventName) {
        document.removeEventListener(eventName, this.onMouseMove);
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