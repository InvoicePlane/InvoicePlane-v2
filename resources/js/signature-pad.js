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

    form?.addEventListener('submit', (event) => {
        if (pad.isEmpty()) {
            event.preventDefault();
            window.alert('Please provide a signature before submitting.');

            return;
        }

        hiddenInput.value = pad.toDataURL('image/png');
    });
});
