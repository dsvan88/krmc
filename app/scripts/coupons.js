class CouponStatusesPad extends Prompt {
    statuses = [
        'ready',
        // 'applied',
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
            const radio = document.createElement('input');
            radio.type = 'radio';
            radio.name = 'status';
            radio.value = this.statuses[x];
            radio.checked = this.input.value === this.statuses[x];
            radio.id = 'popup__status-'+this.statuses[x];

            const label = document.createElement('label');
            label.classList.add('statuses__label');
            label.htmlFor = radio.id;        
            label.innerText = this.statuses[x];
            label.classList.add(this.statuses[x]);
            item.append(radio);
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
    const result = await couponsStatusesPad({value: target.dataset.couponStatus});
    const fd = new FormData();
    fd.append('couponId', target.dataset.couponId);
    fd.append('status', result);
    const answer = await this.request({url: target.dataset.actionClick, data: fd});
    console.log(answer);
}
