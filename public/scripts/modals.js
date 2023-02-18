class ModalWindow {

	modal = null;
	modalIndex = null;
	commonOverlay = null;
	currentOverlay = null;
	headerTitle = null;
	formSubmitHandler = null;
	context = null;
	content = null;

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
	};
	fill({ html = "", title = "", buttons = [], submit = null, context = null }) {
		let modalContainer = null;
		if (html) {
			this.content = this.content || this.modal.querySelector('.modal__container');
			this.content.innerHTML = html;
		}
		
		if (title)
			this.modal.querySelector('.modal__title').innerText = title;
		
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

		this.headerTitle = document.createElement("h3");
		this.headerTitle.className = 'modal__title';
		this.headerTitle.innerHTML = 'Завантаження...';

		let modalClose = document.createElement("i");
		modalClose.className = "fa fa-window-close modal__close";
		modalHeader.append(this.headerTitle);
		modalHeader.append(modalClose);

		this.modal = document.createElement("div");
		this.modal.className = "modal";
		this.modal.append(modalHeader);
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
}