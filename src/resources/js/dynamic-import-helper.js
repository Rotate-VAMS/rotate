// Import all Vue components from both Pages and Modules
const pages = import.meta.glob('/resources/js/Pages/**/*.vue');
const modulePages = import.meta.glob('/resources/js/Modules/**/Pages/*.vue');

export default async function resolvePageComponent(name) {
    // First try to find the component in regular Pages
    const pagePath = `/resources/js/Pages/${name}.vue`;
    if (pages[pagePath]) {
        return await pages[pagePath]();
    }

    // If not found in Pages, try to find in Modules
    const modulePath = `/resources/js/Modules/${name}.vue`;
    if (modulePages[modulePath]) {
        return await modulePages[modulePath]();
    }

    // If still not found, try to find in module Pages directories
    for (const path in modulePages) {
        const moduleName = path.split('/').pop().replace('.vue', '');
        if (moduleName === name) {
            return await modulePages[path]();
        }
    }

    throw new Error(`Unknown dynamic import path for component: ${name}`);
}