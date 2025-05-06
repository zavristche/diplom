<script setup>
defineProps({
  user: {
    type: String,
  },
});
</script>

<template>
  <div class="user-card">
    <router-link :to="`/profile/${user.id}`" class="author">
      <img :src="user.avatar" class="user-card__avatar" alt="User avatar" />
    </router-link>
    <div class="user-card__info">
      <div class="user-card__title">
        <router-link :to="`/profile/${user.id}`" class="author">
          <h3>{{ user.login }}</h3>
        </router-link>
        <div class="user-card__status">{{ user.status }}</div>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
@use "../assets/styles/_variables.scss" as *;

.user-card {
  display: inline-flex;
  justify-content: center;
  align-items: center;
  gap: 20px;
  width: 373px;
  background: $background;
  border-radius: $border;
  box-shadow: $shadow;
  padding: 10px;

  .user-card__avatar {
    width: 125px;
    height: 125px;
    padding: 10px;
    border-radius: 100px;
    object-fit: cover;
  }

  .user-card__info {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: flex-start;
    gap: 5px;
    width: 224px;
  }

  .user-card__title {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 5px;

    h3 {
      color: $dark-text;
      font-family: Rubik, sans-serif;
      font-size: 16px;
      font-weight: 500;
      word-wrap: break-word;
      margin: 0;
    }
  }

  .user-card__status {
    color: $text-info;
    font-family: Rubik, sans-serif;
    font-size: 14px;
    font-weight: 400;
    word-wrap: break-word;
  }
}
</style>
