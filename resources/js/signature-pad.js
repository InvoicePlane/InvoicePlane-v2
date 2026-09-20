import SignaturePad from 'signature_pad';

document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('signature-pad');
    if (!canvas) {
        return;
    }

    const resizeCanvas = () => {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        // clientWidth/clientHeight are driven by CSS (w-full / fixed height
        // on the element), not by the canvas.width/height backing-store
        // attributes set below — so they stay stable across repeated calls
        // instead of compounding with the device pixel ratio each time.
        const cssWidth = canvas.clientWidth;
        const cssHeight = canvas.clientHeight;
        canvas.width = cssWidth * ratio;
        canvas.height = cssHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        pad.clear();
    };

    const pad = new SignaturePad(canvas);
    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();

    const clearButton = document.getElementById('signature-clear');
    clearButton?.addEventListener('click', () => pad.clear());

    const form = document.getElementById('signature-form');
    const hiddenInput = document.getElementById('signature_data');

    // Pointer-drawn signing is unusable without a mouse/touchscreen/pen, so a
    // keyboard-accessible typed alternative renders the entered text onto an
    // offscreen canvas and submits that as the same image data URL format.
    const modeRadios = document.querySelectorAll('input[name="signature_mode"]');
    const drawPanel = document.getElementById('signature-draw-panel');
    const typePanel = document.getElementById('signature-type-panel');
    const typeInput = document.getElementById('signature-text');

    const currentMode = () =>
        document.querySelector('input[name="signature_mode"]:checked')?.value ?? 'draw';

    const updatePanels = () => {
        const isTypeMode = currentMode() === 'type';
        drawPanel?.classList.toggle('hidden', isTypeMode);
        typePanel?.classList.toggle('hidden', !isTypeMode);
    };

    modeRadios.forEach((radio) => radio.addEventListener('change', updatePanels));
    updatePanels();

    const typedSignatureDataUrl = (text) => {
        const offscreen = document.createElement('canvas');
        offscreen.width = 600;
        offscreen.height = 200;

        const ctx = offscreen.getContext('2d');
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, offscreen.width, offscreen.height);
        ctx.fillStyle = '#111827';
        ctx.font = "48px 'Brush Script MT', cursive";
        ctx.textBaseline = 'middle';
        ctx.fillText(text, 20, offscreen.height / 2);

        return offscreen.toDataURL('image/png');
    };

    form?.addEventListener('submit', (event) => {
        if (currentMode() === 'type') {
            const text = typeInput?.value.trim();
            if (!text) {
                event.preventDefault();
                window.alert('Please type your signature before submitting.');

                return;
            }

            hiddenInput.value = typedSignatureDataUrl(text);

            return;
        }

        if (pad.isEmpty()) {
            event.preventDefault();
            window.alert('Please provide a signature before submitting.');

            return;
        }

        hiddenInput.value = pad.toDataURL('image/png');
    });
});
