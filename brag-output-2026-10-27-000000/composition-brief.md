# Hyperframes Composition Brief: TallerLab

## Objective
Create a polished short launch video for TallerLab, a Spanish-language service workshop operations app.

## Output
- Composition directory: `brag-output-2026-10-27-000000/composition/`
- Rendered video: `brag-output-2026-10-27-000000/brag.mp4`
- Format: landscape — 1920x1080
- Duration: 20 seconds

## Source Material
- Project root: `C:\laragon2\www\taller`
- Primary files read: `resources/views/workshop.blade.php`, `resources/views/service-order-status.blade.php`, `resources/css/app.css`
- Product name: TallerLab
- UI brand descriptor: “Gestión técnica”. No marketing tagline found.
- Key UI moments: workshop dashboard, Nueva recepción, service order workflow, public order status progress, billing.
- Copy grounded in source: “Nueva recepción”, “Órdenes”, “En diagnóstico”, “En reparación”, “Listo para entrega”, “Inventario”, “Facturación”, “TallerLab · Gestión técnica”.

## Creative Direction
- Tone preset: polished
- Creative direction: quiet, capable workshop operations film
- Interpretation: smooth precise cuts; restrained typography and motion; let the actual interface and status colors carry the energy.
- Angle: Show the complete repair-shop loop from intake to customer-visible ready status, with actual screens and fictional stand-in records.
- Hook: “De recibido a listo para entrega.”
- Outro: “Listo para entrega” with TallerLab identity.
- Avoid: generic SaaS language, abstract filler, real customer data, unrelated redesign.

## Visual Identity
- Background: #f5f7fa
- Text: #15233b
- Accent: #315cf5; ready-state teal #16a895
- Display font: Manrope
- Body font: DM Sans
- Visual references: fixed dark navy sidebar, pale dashboard canvas, rounded white cards, blue action buttons, teal ready status.

## Storyboard
Use `brag-plan.md` as the creative contract.
1. The shop, in motion — 3s — real dashboard and hook.
2. Intake — 4s — actual Nueva recepción form with fictional data.
3. Service order — 5s — order detail with status and work/parts.
4. Customer update — 4s — public status page progressing to ready.
5. Close the loop — 4s — TallerLab lockup and grounded navigation labels.

## Audio
- Audio role: warm bed with sparse interface accents.
- Music: `assets/music/happy-beats-business-moves-vol-1-by-ende-dot-app.mp3`.
- Music treatment: soft opening, gentle lift, fade under the lockup.
- Music cue guidance: detect at composition time; prioritize readable text holds.
- Audio-reactive treatment: subtle glow on the blue/teal status track if supported.
- Audio-coupled moments: intake confirmation, status changes, ready-state.
- SFX selection guidance: low-volume, restrained, motion-matched; no arcade-like sounds.
- SFX analysis guidance: bundled `assets/sfx/sfx-analysis.md`.
- Exact SFX choice: defer to Hyperframes after animation is implemented.

## Hyperframes Instructions
Use current installed Hyperframes composition contract, motion, look, registry, media-use, and CLI guidance. Recreate the actual UI in the video and use only fictional stand-in records. Keep all text readable and factual claims grounded in the project. Run `npx hyperframes check` before render.
