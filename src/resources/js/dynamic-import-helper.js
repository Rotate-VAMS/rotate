const pages = import.meta.glob('/resources/js/Modules/**/*.vue');

export default async function resolvePageComponent(name) {
    const path = `/resources/js/Modules/${name}.vue`;
    if (!pages[path]) {
        throw new Error(`Unknown dynamic import path for module: ${name}`);
    }

    return await pages[path]();
}