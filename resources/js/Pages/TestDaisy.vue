<template>
  <Head title="Test DaisyUI" />
  
  <div class="min-h-screen bg-base-200 p-8">
    <div class="max-w-4xl mx-auto">
      <h1 class="text-4xl font-bold text-center mb-8">Test DaisyUI Components</h1>
      
      <!-- Alert -->
      <div class="alert alert-success mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>DaisyUI is working correctly!</span>
      </div>
      
      <!-- Buttons -->
      <div class="flex gap-4 mb-6">
        <button class="btn btn-primary">Primary</button>
        <button class="btn btn-secondary">Secondary</button>
        <button class="btn btn-accent">Accent</button>
        <button class="btn btn-ghost">Ghost</button>
      </div>
      
      <!-- Card -->
      <div class="card w-96 bg-base-100 shadow-xl mb-6">
        <div class="card-body">
          <h2 class="card-title">Card Title</h2>
          <p>This is a DaisyUI card component.</p>
          <div class="card-actions justify-end">
            <button class="btn btn-primary">Buy Now</button>
          </div>
        </div>
      </div>
      
      <!-- Badge -->
      <div class="flex gap-2 mb-6">
        <div class="badge badge-primary">Primary</div>
        <div class="badge badge-secondary">Secondary</div>
        <div class="badge badge-accent">Accent</div>
        <div class="badge badge-ghost">Ghost</div>
      </div>
      
      <!-- Progress -->
      <progress class="progress progress-primary w-56 mb-6" value="70" max="100"></progress>
      
      <!-- Toggle -->
      <div class="form-control w-52">
        <label class="cursor-pointer label">
          <span class="label-text">Remember me</span>
          <input type="checkbox" class="toggle toggle-primary" checked />
        </label>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
</script>