const checkOwnership = (authStore, entity) => {
    if (!authStore.isAuthenticated || !authStore.userId || !entity) {
      return false;
    }
    return authStore.userId === entity.user_id;
  };