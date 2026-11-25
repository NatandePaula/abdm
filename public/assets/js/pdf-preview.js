(function () {
    const script = document.currentScript;
    const viewerBaseUrl = script ? script.dataset.viewerUrl : '';

    window.abrirPreviaPdf = function (pdfUrl) {
        if (!viewerBaseUrl || !pdfUrl) {
            return false;
        }

        const sourceUrl = new URL(pdfUrl, window.location.href);
        sourceUrl.searchParams.set('_', Date.now().toString());

        const viewerUrl = new URL(viewerBaseUrl, window.location.href);
        viewerUrl.searchParams.set('file', sourceUrl.toString());

        const previewWindow = window.open(viewerUrl.toString(), '_blank');
        if (!previewWindow) {
            window.alert('O navegador bloqueou a nova aba de impressão. Permita pop-ups para este sistema e tente novamente.');
            return false;
        }

        previewWindow.opener = null;
        return false;
    };
})();
