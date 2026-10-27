import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import Error from '@/Pages/Error.vue';

describe('Error.vue', () => {
    it('renders 404 error correctly', () => {
        const wrapper = mount(Error, {
            props: {
                status: 404,
            },
        });

        expect(wrapper.text()).toContain('Page Not Found');
        expect(wrapper.text()).toContain('Sorry, the page you are looking for could not be found.');
    });

    it('renders 403 error correctly', () => {
        const wrapper = mount(Error, {
            props: {
                status: 403,
            },
        });

        expect(wrapper.text()).toContain('Access Denied');
        expect(wrapper.text()).toContain('Sorry, you are forbidden from accessing this page or resource.');
    });

    it('renders 500 error correctly', () => {
        const wrapper = mount(Error, {
            props: {
                status: 500,
            },
        });

        expect(wrapper.text()).toContain('Server Error');
        expect(wrapper.text()).toContain('Whoops, something went wrong on our servers.');
    });
    
    it('renders 503 error correctly', () => {
        const wrapper = mount(Error, {
            props: {
                status: 503,
            },
        });

        expect(wrapper.text()).toContain('Service Unavailable');
        expect(wrapper.text()).toContain('Sorry, we are doing some maintenance. Please check back soon.');
    });
});
