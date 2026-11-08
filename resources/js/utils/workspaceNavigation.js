export function isWorkspaceDestination(value, origin) {
    try {
        const href = typeof value === 'string' ? value : value?.href ?? value?.pathname;
        if (!href) return false;
        const url = new URL(href, origin);
        return url.origin === origin && /^\/admin(?:\/|$)/.test(url.pathname) && !/^\/admin\/(login|logout)(?:\/|$)/.test(url.pathname);
    } catch { return false; }
}
