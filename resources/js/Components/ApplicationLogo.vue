<template>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400">

        <!-- circle background -->
        <circle cx="200" cy="200" r="190" fill="#fdf6e3" stroke="#333" stroke-width="4"/>

        <!-- sun -->
        <circle cx="290" cy="90" r="25" fill="#FDB813"/>
        <g stroke="#FDB813" stroke-width="3">
            <line x1="290" y1="50" x2="290" y2="70"/>
            <line x1="290" y1="110" x2="290" y2="130"/>
            <line x1="250" y1="90" x2="270" y2="90"/>
            <line x1="310" y1="90" x2="330" y2="90"/>
        </g>

        <!-- sea -->
        <path d="M40 240 Q120 220 200 240 T360 240 L360 320 L40 320 Z"
              fill="#4ec3e0"/>

        <!-- sand -->
        <rect x="40" y="240" width="320" height="80" fill="#f4d6a0"/>

        <!-- palm -->
        <rect x="90" y="180" width="8" height="70" fill="#8b5a2b"/>
        <g fill="#2e8b57">
            <ellipse cx="95" cy="170" rx="30" ry="10"/>
            <ellipse cx="95" cy="170" rx="10" ry="30"/>
        </g>

        <!-- chair -->
        <rect x="170" y="230" width="60" height="40" fill="#ff7f50"/>
        <line x1="170" y1="230" x2="150" y2="200" stroke="#333" stroke-width="3"/>
        <line x1="230" y1="230" x2="250" y2="200" stroke="#333" stroke-width="3"/>

        <!-- head -->
        <circle cx="200" cy="200" r="10" fill="#f1c27d"/>

        <!-- text -->
        <text x="200" y="120"
              font-size="38"
              text-anchor="middle"
              font-family="Arial"
              fill="#ff7f50"
              font-weight="bold">
            HOLIDAY
        </text>

    </svg>
</template>
