import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function usePermissions() {
    const page = usePage();

    const roles = computed(() => {
        return page.props.user_roles || [];
    });

    const permissions = computed(() => {
        const perms = page.props.permissions || [];
        // Support both array of strings and array of objects
        return perms.map(p => typeof p === 'string' ? p : p.name);
    });

    const hasRole = (roleName) => {
        if (!roleName) return false;
        const target = roleName.toLowerCase().trim();
        return roles.value.some(r => {
            const current = (typeof r === 'string' ? r : (r?.name || '')).toLowerCase().trim();
            return current === target || current === target + 's' || current + 's' === target;
        });
    };

    const hasAnyRole = (roleNames) => {
        return roleNames.some(role => hasRole(role));
    };

    const can = (permissionName) => {
        // Only super admin has blanket access bypass; super user must have active permissions
        if (hasRole('super admin')) {
            return true;
        }
        if (!permissionName) {
            return true;
        }
        const permNormalized = permissionName.toLowerCase().trim();
        return permissions.value.some(p => typeof p === 'string' && p.toLowerCase().trim() === permNormalized);
    };

    return {
        roles,
        permissions,
        hasRole,
        hasAnyRole,
        can
    };
}
