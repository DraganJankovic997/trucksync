export function hasRole(user, roleName) {
  if (!user) {
    return false;
  }

  if (user.role === roleName || user.role?.name === roleName) {
    return true;
  }

  if (!Array.isArray(user.roles)) {
    return false;
  }

  return user.roles.some(role => role === roleName || role?.name === roleName);
}
