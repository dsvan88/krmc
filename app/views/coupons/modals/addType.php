<form class="modal__form" method="POST" data-action-submit="/coupons/addType">
    <fieldset>
        <legend class="modal__subtitle"><?= $subtitle ?></legend>

        <div class="modal__row">
            <input class="modal__input" required type="text" name="name" placeholder="Name" required />
        </div>
        <div class="modal__row">
            <div class="modal__label">Discount</div>
            <div class="modal__value flex">
                <input class="modal__input" required type="number" min="0" name="discount" step="1" value="100" autofocus />
                <select class="modal__select" name="discount_type">
                    <option value="%" selected >%</option>
                    <option value="₴">₴</option>
                    <option value="$">$</option>
                    <option value="€">€</option>
                </select>
            </div>
        </div>
        <div class="modal__row">
            <div class="modal__label">Price</div>
            <div class="modal__value">
                <input class="modal__input" required type="number" min="0" name="price" step="1" value="300" placeholder="Price" />
            </div>
        </div>
        <div class="modal__buttons">
            <button type="submit" class="positive"><?= $texts['SubmitLabel'] ?></button>
        </div>
    </fieldset>
</form>