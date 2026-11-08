export function linkPreview(value) {
    try {
        const url = new URL(value?.trim());
        if (url.protocol !== 'https:' || url.username || url.password) return null;
        return { href: url.href, domain: url.hostname, path: `${url.pathname}${url.search}${url.hash}` };
    } catch { return null; }
}
