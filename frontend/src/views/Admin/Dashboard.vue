<template>
  <v-container>
    <v-row>
      <v-col cols="6">
        <div class="rounded" style="height: 400px;">
          <adoption-stat />
        </div>
      </v-col>
      <v-col cols="6">
        <div class="rounded" style="height: 400px;">
          <rescue-stat />
        </div>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import adoptionStat from '@/components/charts/adoptionStat.vue';
import rescueStat from '@/components/charts/rescueStat.vue';
</script>
