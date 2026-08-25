// composables/ability.client.js
import { Ability } from '@casl/ability';

export const ability = new Ability([]);

/**
 * Update the CASL ability instance from permissions
 * @param {string[]} permissions - Array of permissions from Pinia store
 */
export function updateAbilityFromPermissions(permissions) {

    const rules = permissions.map((permission) => {
        const [subject, action] = permission.split('.').reverse(); // e.g., "message.view" -> { action: 'view', subject: 'message' }
        return { action, subject };
    });

    ability.update(rules);
}
