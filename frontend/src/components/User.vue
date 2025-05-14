<script setup>
defineProps({
  user: {
    type: Object,
    required: true
  }
});
</script>

<template>
  <article class="user-card">
    <router-link :to="`/profile/${user.id}`" class="user-card__avatar-link">
      <img :src="user.avatar" :alt="`${user.login} avatar`" class="user-card__avatar" />
    </router-link>
    
    <div class="user-card__content">
      <router-link :to="`/profile/${user.id}`" class="user-card__name">
        <h3>{{ user.login }}</h3>
      </router-link>
      <p class="user-card__status">{{ user.status }}</p>
    </div>
  </article>
</template>

<style lang="scss" scoped>
@use "../assets/styles/_variables.scss" as *;

.user-card {
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 1.25rem;
  align-items: center;
  width: 100%;
  padding: 0.625rem;
  background: $background;
  border-radius: $border;
  box-shadow: $shadow;

  &__avatar {
    width: 7.8125rem;
    height: 7.8125rem;
    padding: 0.625rem;
    border-radius: 50%;
    object-fit: cover;
  }

  &__content {
    display: grid;
    gap: 0.3125rem;
  }

  &__name {
    text-decoration: none;
    
    h3 {
      margin: 0;
      color: $dark-text;
      font-family: Rubik, sans-serif;
      font-size: 1rem; 
      font-weight: 500;
      word-wrap: break-word;
    }
  }

  &__status {
    margin: 0;
    color: $text-info;
    font-family: Rubik, sans-serif;
    font-size: 0.875rem;
    font-weight: 400;
    word-wrap: break-word;
  }

  @media (max-width: 768px) {

    &__avatar {
      width: 6.25rem;
      height: 6.25rem;
      margin: 0 auto;
    }

    &__content {
      justify-items: flex-start;
    }
  }
}
</style>