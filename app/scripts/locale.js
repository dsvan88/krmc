class i18n {
    static dict = new Map();
    static isLoading = false;

    static async init({lang = 'uk', module = ''} = {}) {
        if (this.isLoading) return;
        this.isLoading = true;

        try {
            let url =  `/get/locale/${lang}`;
            if (module){
                url += `?module=${module}`;
            }
            const response = await request({ url: url });
            if (response.dict) {
                const _dict = Object.entries(response.dict);
                for (const [k, v] of _dict) {
                    this.dict.set(k, v)
                }
            }
        } catch (e) {
            console.error("Localization failed", e);
        } finally {
            this.isLoading = false;
        }
    }

    static translate(text) {
        return this.dict.get(text) || text;
    }
}

async function localeInit({ lang = 'uk', module = '' } = {}) {
    return await i18n.init({lang: lang, module: module});
}

function __(text) {
    return i18n.translate(text);
}
