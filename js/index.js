document.addEventListener('DOMContentLoaded', () => {
    initializeButtonHoverEffects();
    initializeFeatureCardEffects();
    initializePreviewButton();
    initializePreviewRotation();
    initializeCodeTypewriter();
});

function initializeButtonHoverEffects() {
    document.querySelectorAll('.btn').forEach((button) => {
        button.addEventListener('mouseenter', function () {
            this.style.transform = 'translateY(-3px)';
            this.style.boxShadow = '0 7px 15px rgba(0, 0, 0, 0.2)';
        });

        button.addEventListener('mouseleave', function () {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 4px 10px rgba(0, 0, 0, 0.1)';
        });
    });
}

function initializeFeatureCardEffects() {
    document.querySelectorAll('.feature-card').forEach((card) => {
        card.addEventListener('mouseenter', function () {
            this.style.transform = 'translateY(-10px)';
        });

        card.addEventListener('mouseleave', function () {
            this.style.transform = 'translateY(0)';
        });
    });
}

function initializePreviewButton() {
    const previewButton = document.querySelector('.preview-button, .preview-btn');
    if (!previewButton) {
        return;
    }

    previewButton.addEventListener('click', () => {
        alert('Live preview functionality would be implemented here!');
    });
}

function initializePreviewRotation() {
    const preview = document.querySelector('.preview-3d, .preview-3d-hologram');
    if (!preview) {
        return;
    }

    let angle = 0;
    setInterval(() => {
        angle += 0.5;
        preview.style.transform = `rotateY(${angle}deg)`;
    }, 100);
}

function initializeCodeTypewriter() {
    const codeBox = document.querySelector('.code-box');
    if (!codeBox) {
        return;
    }

    const codeLines = [
        '&lt;html&gt;',
        '&lt;body&gt;',
        '&lt;h1&gt;<span class="text">Hello World!</span>&lt;/h1&gt;',
        '&lt;p&gt;<span class="text">This is a live preview of your code.</span>&lt;/p&gt;',
        '&lt;/body&gt;',
        '&lt;/html&gt;'
    ];

    let lineIndex = 0;
    let charIndex = 0;
    let currentLine = '';
    codeBox.innerHTML = '';

    function typeCode() {
        if (lineIndex < codeLines.length) {
            const fullLine = codeLines[lineIndex];
            if (charIndex < fullLine.length) {
                currentLine += fullLine.charAt(charIndex);
                codeBox.innerHTML = `${codeLines.slice(0, lineIndex).join('<br>')}<br>${currentLine}`;
                charIndex += 1;
                setTimeout(typeCode, 35);
                return;
            }

            lineIndex += 1;
            charIndex = 0;
            currentLine = '';
            setTimeout(typeCode, 300);
            return;
        }

        setTimeout(() => {
            codeBox.innerHTML = '';
            lineIndex = 0;
            charIndex = 0;
            currentLine = '';
            typeCode();
        }, 2000);
    }

    typeCode();
}
