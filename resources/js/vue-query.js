import { VueQueryPlugin, QueryClient } from '@tanstack/vue-query';

const queryClient = new QueryClient();

export function installVueQuery(app) {
    app.use(VueQueryPlugin, { queryClient });
}
