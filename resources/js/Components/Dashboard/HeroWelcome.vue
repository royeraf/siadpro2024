<script setup>
defineProps({
    userName: {
        type: String,
        default: 'Docente',
    },
});
</script>

<template>
    <div class="mb-6 pt-1">
        <div class="hero-welcome-card flex flex-col justify-between">
            <div class="hero-welcome-content w-full sm:max-w-[65%]">
                <h2 class="hero-title mb-2 font-extrabold text-slate-900 tracking-tight">Bienvenido(a)</h2>
                <p class="hero-subtitle mb-0 text-slate-600 text-sm sm:text-base leading-relaxed">
                    Hola, <strong class="text-slate-800 font-bold">{{ userName }}</strong>. Aquí encontrarás recursos y herramientas para fortalecer las experiencias de aprendizaje de tus niños y niñas.
                </p>
            </div>
            <img 
                src="/img/dashboard/1553253269.svg" 
                alt="Bienvenida" 
                class="hero-welcome-img hidden sm:block pointer-events-none"
            />
        </div>
    </div>
</template>

<style scoped>
.hero-welcome-card {
    background: linear-gradient(135deg, #EEF4FF 0%, #F5F3FF 100%);
    border: 1.5px solid #E0E7FF;
    border-radius: 24px;
    padding: 1.85rem 2.25rem;
    position: relative;
    overflow: hidden;
    min-height: 165px;
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.05);
}

.hero-welcome-content {
    position: relative;
    z-index: 2;
    max-width: 65%;
}

.hero-title {
    font-size: 1.75rem;
    color: #1E1B4B;
    line-height: 1.2;
}

.hero-subtitle {
    max-width: 650px;
}

.hero-welcome-img {
    position: absolute;
    right: 25px;
    bottom: 0;
    max-height: 155px;
    max-width: 32%;
    object-fit: contain;
    z-index: 1;
    pointer-events: none;
}
</style>
