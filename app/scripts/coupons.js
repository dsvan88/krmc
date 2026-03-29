class CouponStatusesPad extends Prompt {
    statuses = [
        'ready',
        'applied',
        'expired',
    ];

    constructor({
        title = "Statuses list",
        text = "Choose status:",
        value = '',
        action = null,
        cancel = null,
    } = {}
    ) {
        super({ title, text, value, action, cancel })

        // this.modifyForCouponStatusesPad().modifyEventsCouponStatusesPad();
        this.modifyForCouponStatusesPad();
        this.dialog.focus();
    }

    modifyForCouponStatusesPad() {
        this.input.classList.add('hidden');
        const inputWrapper = this.input.closest('.popup__input-wrapper');

        inputWrapper.classList.add('statuses');

        const statusesPad = document.createElement('ul');
        statusesPad.classList.add('statuses__list');

        const len = this.statuses.length;
        for (let x = 0; x < len; x++) {
            const item = document.createElement('li');
            item.classList.add('statuses__item');

            const label = document.createElement('label');

            const radio = document.createElement('input');
            radio.type = 'radio';
            radio.name = 'status';
            radio.value = this.statuses[x];
            radio.checked = this.input.value === this.statuses[x];

            const title = document.createElement('span');
            title.innerText = this.statuses[x];
            label.append(radio);
            label.append(title);
            item.append(label);
            statusesPad.append(item);
        }

        inputWrapper.append(statusesPad);
        return this;
    }
    submit(event) {
        const checked = event.target.querySelector('input[type=radio]:checked');
        this.input.value = checked.value;
        return super.submit();
    }
}

async function couponsStatusesPad(options = {}) {
    return await new Promise((r) => {
        options.action = r;
        new CouponStatusesPad(options);
    }).then();
}

actionHandler.couponChangeStatus = async function (target) {
    const result = await couponsStatusesPad({value: 'applied'});
    console.log(result);
}
