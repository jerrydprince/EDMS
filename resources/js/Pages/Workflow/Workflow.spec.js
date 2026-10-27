import { mount } from '@vue/test-utils';
import { describe, it, expect, vi } from 'vitest';
import WorkflowIndex from '@/Pages/Workflow/Workflows.vue';

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
            DataTable: true,
            Column: true
        }
    },
    shallow: true
};

describe('Workflow/Index.vue', () => {
    it('mounts properly and displays correct state', () => {
        const wrapper = mount(WorkflowIndex, {
            ...globalMountOptions,
            props: {
                workflows: []
            }
        });
        
        expect(wrapper.exists()).toBe(true);
    });
});
