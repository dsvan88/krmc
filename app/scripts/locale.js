class i18n {
    static dict = new Map();
    static isLoading = false;

    static async init(lang = 'uk') {
        if (this.isLoading) return;
        this.isLoading = true;

        try {
            const response = await request({ url: `/locale/get/${lang}` });
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

function __(text) {
    return i18n.translate(text);
}

async function localeInit(lang = 'uk') {
    return await i18n.init(lang);
}