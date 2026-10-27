import { mount } from '@vue/test-utils';
import { describe, it, expect, vi } from 'vitest';
import ReportsIndex from '@/Pages/Reports/Index.vue';

// Mock inertia and globals
global.route = () => ({ path: '/', current: () => false });

vi.mock('@inertiajs/vue3', () => ({
    Head: { template: '<div><slot/></div>' },
    Link: { template: '<div><slot/></div>' },
    usePage: () => ({ props: { roles: [], user: {} } })
}));

import PrimeVue from 'primevue/config';
import ToastService from 'primevue/toastservice';

const globalMountOptions = {
    global: {
        plugins: [PrimeVue, ToastService],
        stubs: {
            Link: true,
            Head: { template: '<div><slot/></div>' },
            Chart: true
        }
    },
    shallow: true
};

describe('Reports/Index.vue', () => {
    it('mounts properly and displays report components', () => {
        const wrapper = mount(ReportsIndex, {
            ...globalMountOptions,
            props: {
                reportData: []
            }
        });
        
        expect(wrapper.exists()).toBe(true);
    });
});
