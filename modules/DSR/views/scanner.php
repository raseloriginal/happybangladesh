<?php $pageTitle = 'QR Scanner'; ?>

<div class="h-full flex flex-col bg-black">
  <!-- Header overlay -->
  <div class="pt-10 pb-4 px-4 flex items-center justify-between z-10 bg-gradient-to-b from-black/80 to-transparent absolute top-0 left-0 w-full">
    <a href="javascript:history.back()" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-white backdrop-blur-md active:bg-white/20">
      <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div class="text-white font-semibold text-lg">Scan Product</div>
    <button class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-white backdrop-blur-md active:bg-white/20">
      <i class="fa-solid fa-bolt"></i>
    </button>
  </div>

  <!-- Camera Area (Mockup) -->
  <div class="flex-1 relative flex flex-col items-center justify-center overflow-hidden">
    <!-- Background camera feed mockup -->
    <div class="absolute inset-0 bg-gray-900">
        <img src="https://images.unsplash.com/photo-1601597111158-2fceff292cdc?auto=format&fit=crop&q=80&w=1080&h=1920" class="w-full h-full object-cover opacity-30 mix-blend-overlay grayscale" alt="camera feed">
    </div>

    <!-- Scanner Frame -->
    <div class="relative w-64 h-64 z-10">
      <!-- 4 corners -->
      <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-brand rounded-tl-xl"></div>
      <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-brand rounded-tr-xl"></div>
      <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-brand rounded-bl-xl"></div>
      <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-brand rounded-br-xl"></div>
      
      <!-- Scanning Laser Animation -->
      <div class="absolute top-0 left-0 w-full h-0.5 bg-brand shadow-[0_0_8px_2px_rgba(37,99,235,0.7)] animate-[scan_2s_ease-in-out_infinite]"></div>
    </div>
    
    <p class="text-white/80 text-sm mt-8 z-10 font-medium tracking-wide">Align QR code within the frame</p>

    <!-- Switch Camera Btn -->
    <button class="mt-6 w-12 h-12 bg-white/10 rounded-full text-white flex items-center justify-center backdrop-blur-md z-10 hover:bg-white/20 transition active:scale-90">
      <i class="fa-solid fa-camera-rotate text-lg"></i>
    </button>
  </div>

  <!-- Bottom Sheet: Recent Scans -->
  <div class="bg-white rounded-t-3xl pt-2 pb-6 px-4 shadow-[0_-10px_40px_rgba(0,0,0,0.15)] z-20 relative">
    <div class="w-10 h-1.5 bg-gray-300 rounded-full mx-auto mb-4"></div>
    <div class="flex items-center justify-between mb-4 px-1">
      <h3 class="font-bold text-gray-800">Recent Scans</h3>
      <span class="text-xs text-brand font-semibold">View All</span>
    </div>
    
    <div class="space-y-3">
      <!-- Item 1 -->
      <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-2xl">
        <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center text-blue-500">
          <i class="fa-solid fa-box-open text-xl"></i>
        </div>
        <div class="flex-1">
          <div class="font-semibold text-sm text-gray-800">Happy Mango Juice 250ml</div>
          <div class="text-[11px] text-gray-500">SKU: HJM-250-102 &bull; 2 mins ago</div>
        </div>
        <div class="text-xs font-bold text-gray-700">12 PCS</div>
      </div>
      
      <!-- Item 2 -->
      <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-2xl">
        <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center text-blue-500">
          <i class="fa-solid fa-box-open text-xl"></i>
        </div>
        <div class="flex-1">
          <div class="font-semibold text-sm text-gray-800">Happy Orange Drink 500ml</div>
          <div class="text-[11px] text-gray-500">SKU: HOD-500-405 &bull; 15 mins ago</div>
        </div>
        <div class="text-xs font-bold text-gray-700">24 PCS</div>
      </div>
    </div>
  </div>
</div>

<style>
@keyframes scan {
  0% { top: 0; opacity: 0; }
  10% { opacity: 1; }
  90% { opacity: 1; }
  100% { top: 100%; opacity: 0; }
}
</style>
