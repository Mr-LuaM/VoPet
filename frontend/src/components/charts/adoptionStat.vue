<template>
    <div>
        <v-row><v-col cols="10">
        <v-autocomplete
        v-model="selectedYear"
        @change="fetchData"
        :items="years"
        label="Select a Year"
        return-object
        variant="solo"
      ></v-autocomplete></v-col>
      <v-col cols="2">
      <v-btn color="primary" @click="downloadReport"><v-icon>mdi-download</v-icon></v-btn>
 </v-col>
 </v-row>
      <div style="height: 400px;">
        <canvas ref="chartCanvas"></canvas>
      </div>
    </div>
  </template>
  
  <script setup>
import { onMounted, ref, watch, computed } from 'vue';
import { Chart, registerables } from 'chart.js';
import axios from 'axios';

Chart.register(...registerables);

const chartCanvas = ref(null);
const chartInstance = ref(null);
const selectedYear = ref(new Date().getFullYear());

const fetchData = async () => {
    const response = await axios.get(`api/monthlyAdoptions?year=${selectedYear.value}`);
    updateChart(response.data);
};

const updateChart = (data) => {
  if (chartInstance.value instanceof Chart) {
    chartInstance.value.destroy();
}


    const ctx = chartCanvas.value.getContext('2d');
    chartInstance.value = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Number of Adoptions',
                data: data.data,
                // Define your backgroundColor, borderColor, etc.
            }],
        },
        options: { /* Define your options */ },
    });
};

// Computed property for years
const currentYear = new Date().getFullYear();
const startYear = 2019;
const years = computed(() => {
  return Array.from({ length: currentYear - startYear + 1 }, (v, k) => `${currentYear - k}`);
});

function downloadReport() {
  axios.get(`/api/monthlyAdoptions?year=${selectedYear.value}`)
    .then((response) => {
      const csvContent = toCSV(response.data.labels, response.data.data);
      triggerDownload(csvContent, `Adoptions-${selectedYear.value}.csv`);
    })
    .catch(error => console.error('Error fetching or downloading report:', error));
}

// Utility function to convert data to CSV format
function toCSV(labels, data) {
  const csvRows = [labels.join(','), data.join(',')];
  return csvRows.join('\n');
}

// Utility function to trigger download of CSV content
function triggerDownload(csvContent, filename) {
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.setAttribute('download', filename);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

onMounted(fetchData);
watch(selectedYear, fetchData);
</script>
