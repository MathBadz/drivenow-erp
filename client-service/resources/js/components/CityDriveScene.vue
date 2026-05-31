<script setup lang="ts">
/**
 * Animated night-city backdrop: a layered skyline silhouette with cars
 * driving across a road. Purely decorative — sits behind hero content.
 * Respects prefers-reduced-motion (cars hold still).
 */
type Car = {
    color: string;
    bottom: string;
    scale: number;
    duration: number;
    delay: number;
    reverse?: boolean;
    opacity?: number;
};

const cars: Car[] = [
    { color: '#f59e0b', bottom: '14px', scale: 1, duration: 13, delay: 0 },
    { color: '#e2e8f0', bottom: '40px', scale: 0.7, duration: 19, delay: 4, opacity: 0.85 },
    { color: '#38bdf8', bottom: '70px', scale: 0.5, duration: 26, delay: 9, reverse: true, opacity: 0.7 },
    { color: '#fb7185', bottom: '20px', scale: 0.85, duration: 16, delay: 7 },
];
</script>

<template>
    <div class="pointer-events-none absolute inset-0 overflow-hidden">
        <!-- Stars -->
        <div class="absolute inset-0">
            <span
                v-for="n in 28"
                :key="n"
                class="twinkle absolute h-px w-px rounded-full bg-white"
                :style="{
                    top: `${(n * 37) % 60}%`,
                    left: `${(n * 53) % 100}%`,
                    opacity: 0.15 + ((n * 13) % 50) / 100,
                    animationDelay: `${(n % 6) * 0.5}s`,
                }"
            />
        </div>

        <!-- Moon glow -->
        <div class="absolute top-10 right-[12%] h-24 w-24 rounded-full bg-amber-200/20 blur-2xl" />
        <div class="absolute top-12 right-[14%] h-10 w-10 rounded-full bg-amber-100/40 blur-[2px]" />

        <!-- Far skyline -->
        <svg class="absolute right-0 bottom-[90px] left-0 w-full" viewBox="0 0 1440 220" preserveAspectRatio="none" style="height: 150px">
            <path
                d="M0,220 V120 H70 V160 H120 V90 H190 V140 H250 V108 H330 V64 H392 V128 H470 V96 H540 V150 H604 V78 H690 V120 H742 V58 H824 V128 H884 V100 H966 V140 H1024 V88 H1104 V120 H1164 V70 H1248 V140 H1308 V110 H1384 V150 H1440 V220 Z"
                fill="#0b1220"
            />
        </svg>

        <!-- Lit windows -->
        <div class="absolute right-0 bottom-[140px] left-0">
            <span
                v-for="n in 22"
                :key="`w${n}`"
                class="absolute h-1 w-1 rounded-[1px] bg-amber-300/60"
                :style="{ left: `${(n * 61) % 98}%`, top: `${(n * 29) % 60}px` }"
            />
        </div>

        <!-- Near skyline -->
        <svg class="absolute right-0 bottom-[88px] left-0 w-full" viewBox="0 0 1440 180" preserveAspectRatio="none" style="height: 120px">
            <path
                d="M0,180 V110 H90 V70 H150 V120 H230 V96 H300 V130 H360 V84 H450 V120 H520 V100 H600 V140 H680 V90 H760 V126 H840 V104 H930 V134 H1010 V94 H1100 V128 H1180 V100 H1270 V132 H1350 V110 H1440 V180 Z"
                fill="#060b15"
            />
        </svg>

        <!-- Road -->
        <div class="absolute right-0 bottom-0 left-0 h-[92px] bg-gradient-to-b from-[#0a1019] to-[#05080f]">
            <div class="road-line absolute top-[46px] right-0 left-0 h-[2px]"></div>
        </div>

        <!-- Cars -->
        <div
            v-for="(car, i) in cars"
            :key="i"
            class="absolute"
            :class="car.reverse ? 'car-rev' : 'car'"
            :style="{
                bottom: car.bottom,
                opacity: car.opacity ?? 1,
                transform: `scale(${car.scale})`,
                animationDuration: `${car.duration}s`,
                animationDelay: `${car.delay}s`,
            }"
        >
            <svg viewBox="0 0 150 60" class="h-7 w-[150px]" :style="car.reverse ? 'transform: scaleX(-1)' : ''">
                <defs>
                    <linearGradient :id="`beam${i}`" x1="0" x2="1" y1="0" y2="0">
                        <stop offset="0%" stop-color="#fde68a" stop-opacity="0.5" />
                        <stop offset="100%" stop-color="#fde68a" stop-opacity="0" />
                    </linearGradient>
                </defs>
                <ellipse cx="150" cy="42" rx="26" ry="5" :fill="`url(#beam${i})`" />
                <path
                    d="M8,46 C8,42 10,40 15,40 L27,40 C31,31 40,25 55,25 L88,25 C100,25 109,31 118,40 L132,42 C137,43 140,45 140,48 C140,51 137,53 133,53 L15,53 C10,53 8,51 8,47 Z"
                    :fill="car.color"
                />
                <path d="M42,40 C46,32 52,29 60,29 L74,29 L74,40 Z" fill="#0b1220" opacity="0.85" />
                <path d="M80,29 L88,29 C99,29 106,33 113,40 L80,40 Z" fill="#0b1220" opacity="0.85" />
                <circle cx="42" cy="53" r="8" fill="#0b1220" stroke="#334155" stroke-width="3" />
                <circle cx="110" cy="53" r="8" fill="#0b1220" stroke="#334155" stroke-width="3" />
                <circle cx="138" cy="44" r="2.5" fill="#fde68a" />
            </svg>
        </div>
    </div>
</template>

<style scoped>
.car {
    left: -10rem;
    animation-name: drive;
    animation-timing-function: linear;
    animation-iteration-count: infinite;
}
.car-rev {
    left: -10rem;
    animation-name: drive;
    animation-timing-function: linear;
    animation-iteration-count: infinite;
}
@keyframes drive {
    from {
        left: -10rem;
    }
    to {
        left: calc(100% + 10rem);
    }
}
.road-line {
    background-image: repeating-linear-gradient(
        to right,
        rgba(245, 158, 11, 0.5) 0,
        rgba(245, 158, 11, 0.5) 22px,
        transparent 22px,
        transparent 44px
    );
    animation: dash 1.2s linear infinite;
}
@keyframes dash {
    from {
        background-position: 0 0;
    }
    to {
        background-position: -44px 0;
    }
}
.twinkle {
    animation: twinkle 3.5s ease-in-out infinite;
}
@keyframes twinkle {
    0%,
    100% {
        opacity: 0.2;
    }
    50% {
        opacity: 0.9;
    }
}
@media (prefers-reduced-motion: reduce) {
    .car,
    .car-rev,
    .road-line,
    .twinkle {
        animation: none;
    }
    .car,
    .car-rev {
        left: 30%;
    }
}
</style>
