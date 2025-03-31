<template>
    <button class="open-modal-btn" @click="isModalOpen = true">Открыть модальное окно</button>
    <Register :isOpen="isModalOpen" @close="isModalOpen = false" />
  </template>
  
  <script setup>
  import { ref } from "vue";
  import Register from "../components/Register.vue";
  
  const isModalOpen = ref(false);
  </script>
  
  <style scoped>
  .open-modal-btn {
    padding: 10px 20px;
    background: #383502;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
  }
  </style>
  