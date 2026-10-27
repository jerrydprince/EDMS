import { mount } from '@vue/test-utils';
import { describe, it, expect, vi } from 'vitest';
import Dashboard from '@/Pages/dashboard/Index.vue';

// Mock inertia and globals
global.route = () => ({ path: '/', current: () => false });

vi.mock('@inertiajs/vue3', () => ({
    Head: { template: '<div><slot/></div>' },
    Link: { template: '<div><slot/></div>' },
    usePage: () => ({ props: { roles: [], user: {} } })
}));

import PrimeVue from 'primevue/config';
import ToastService from 'primevue/toastservice';

// Mock inertia links
const globalMountOptions = {
    global: {
        plugins: [PrimeVue, ToastService],
        stubs: {
            Link: true,
            Head: { template: '<div><slot/></div>' },
            BranchDetails: true
        }
    },
    shallow: true
};

describe('Dashboard/Index.vue', () => {
    it('mounts properly and handles empty state', () => {
        const wrapper = mount(Dashboard, {
            ...globalMountOptions,
            props: {
                metrics: {
                    pendingTasks: 0,
                    completedTasks: 0,
                    activeWorkflows: 0
                },
                branch: { data: [] },
                auth: {
                    user: { name: 'Test User' }
                }
            }
        });
        
        expect(wrapper.exists()).toBe(true);
    });
});
