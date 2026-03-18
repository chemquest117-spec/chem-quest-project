@props(['size' => 'md'])

@php
     $sizes = [
          'xs' => 'w-6 h-6',
          'sm' => 'w-8 h-8',
          'md' => 'w-10 h-10',
          'lg' => 'w-16 h-16',
          'xl' => 'w-20 h-20',
          '2xl' => 'w-28 h-28',
     ];
     $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center justify-center ' . $sizeClass]) }}>
     <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
          <defs>
               <!-- Gradient for the liquid -->
               <linearGradient id="liquidGrad" x1="30" y1="90" x2="70" y2="50">
                    <stop offset="0%" stop-color="#10b981" />
                    <stop offset="50%" stop-color="#06b6d4" />
                    <stop offset="100%" stop-color="#8b5cf6" />
               </linearGradient>
               <!-- Glow effect -->
               <radialGradient id="glowGrad" cx="50%" cy="70%" r="40%">
                    <stop offset="0%" stop-color="#10b981" stop-opacity="0.4" />
                    <stop offset="100%" stop-color="#10b981" stop-opacity="0" />
               </radialGradient>
               <!-- Flask body clip -->
               <clipPath id="flaskClip">
                    <path
                         d="M38 38 L38 12 L62 12 L62 38 L80 72 Q84 80 78 86 Q74 90 66 90 L34 90 Q26 90 22 86 Q16 80 20 72 Z" />
               </clipPath>
          </defs>

          <!-- Soft glow behind flask -->
          <circle cx="50" cy="65" r="35" fill="url(#glowGrad)" />

          <!-- Flask body outline -->
          <path d="M38 38 L38 12 L62 12 L62 38 L80 72 Q84 80 78 86 Q74 90 66 90 L34 90 Q26 90 22 86 Q16 80 20 72 Z"
               stroke="url(#liquidGrad)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
               fill="none" />

          <!-- Flask neck ring -->
          <rect x="36" y="10" width="28" height="4" rx="2" stroke="url(#liquidGrad)" stroke-width="2" fill="none" />

          <!-- Animated liquid fill -->
          <g clip-path="url(#flaskClip)">
               <path d="M16 68 Q28 62 38 68 Q50 74 62 68 Q72 62 84 68 L84 92 L16 92 Z" fill="url(#liquidGrad)"
                    opacity="0.3">
                    <animate attributeName="d" values="M16 68 Q28 62 38 68 Q50 74 62 68 Q72 62 84 68 L84 92 L16 92 Z;
                            M16 66 Q28 72 38 66 Q50 60 62 66 Q72 72 84 66 L84 92 L16 92 Z;
                            M16 68 Q28 62 38 68 Q50 74 62 68 Q72 62 84 68 L84 92 L16 92 Z" dur="3s"
                         repeatCount="indefinite" />
               </path>
               <path d="M16 72 Q30 66 42 72 Q54 78 66 72 Q78 66 84 72 L84 92 L16 92 Z" fill="url(#liquidGrad)"
                    opacity="0.6">
                    <animate attributeName="d" values="M16 72 Q30 66 42 72 Q54 78 66 72 Q78 66 84 72 L84 92 L16 92 Z;
                            M16 74 Q30 80 42 74 Q54 68 66 74 Q78 80 84 74 L84 92 L16 92 Z;
                            M16 72 Q30 66 42 72 Q54 78 66 72 Q78 66 84 72 L84 92 L16 92 Z" dur="2.5s"
                         repeatCount="indefinite" />
               </path>
          </g>

          <!-- Bubbles rising inside flask -->
          <circle cx="42" cy="78" r="2" fill="#34d399" opacity="0.7">
               <animate attributeName="cy" values="78;55;40" dur="2.5s" repeatCount="indefinite" />
               <animate attributeName="opacity" values="0.7;0.5;0" dur="2.5s" repeatCount="indefinite" />
          </circle>
          <circle cx="55" cy="82" r="1.5" fill="#22d3ee" opacity="0.6">
               <animate attributeName="cy" values="82;60;45" dur="3s" repeatCount="indefinite" />
               <animate attributeName="opacity" values="0.6;0.4;0" dur="3s" repeatCount="indefinite" />
          </circle>
          <circle cx="48" cy="85" r="2.5" fill="#a78bfa" opacity="0.5">
               <animate attributeName="cy" values="85;65;50" dur="3.5s" repeatCount="indefinite" />
               <animate attributeName="opacity" values="0.5;0.3;0" dur="3.5s" repeatCount="indefinite" />
          </circle>

          <!-- Atom orbit around flask top -->
          <g transform="translate(50, 26)">
               <!-- Orbit rings -->
               <ellipse cx="0" cy="0" rx="14" ry="5" stroke="#06b6d4" stroke-width="1" fill="none" opacity="0.4"
                    transform="rotate(-30)" />
               <ellipse cx="0" cy="0" rx="14" ry="5" stroke="#8b5cf6" stroke-width="1" fill="none" opacity="0.4"
                    transform="rotate(30)" />

               <!-- Orbiting electrons -->
               <circle r="2" fill="#06b6d4">
                    <animateMotion dur="2s" repeatCount="indefinite"
                         path="M14,0 A14,5 -30 1,1 -14,0 A14,5 -30 1,1 14,0" />
               </circle>
               <circle r="1.5" fill="#8b5cf6">
                    <animateMotion dur="2.8s" repeatCount="indefinite"
                         path="M-14,0 A14,5 30 1,1 14,0 A14,5 30 1,1 -14,0" />
               </circle>

               <!-- Center nucleus -->
               <circle cx="0" cy="0" r="3" fill="url(#liquidGrad)" />
          </g>

          <!-- Small sparkle accents -->
          <g opacity="0.6">
               <line x1="82" y1="30" x2="88" y2="30" stroke="#10b981" stroke-width="1.5" stroke-linecap="round">
                    <animate attributeName="opacity" values="0.6;0;0.6" dur="2s" repeatCount="indefinite" />
               </line>
               <line x1="85" y1="27" x2="85" y2="33" stroke="#10b981" stroke-width="1.5" stroke-linecap="round">
                    <animate attributeName="opacity" values="0.6;0;0.6" dur="2s" repeatCount="indefinite" />
               </line>
               <line x1="14" y1="50" x2="20" y2="50" stroke="#8b5cf6" stroke-width="1.5" stroke-linecap="round">
                    <animate attributeName="opacity" values="0;0.6;0" dur="2.5s" repeatCount="indefinite" />
               </line>
               <line x1="17" y1="47" x2="17" y2="53" stroke="#8b5cf6" stroke-width="1.5" stroke-linecap="round">
                    <animate attributeName="opacity" values="0;0.6;0" dur="2.5s" repeatCount="indefinite" />
               </line>
          </g>
     </svg>
</div>