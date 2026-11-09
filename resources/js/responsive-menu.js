export default function responsiveMenu(lockScroll = true) {
    return {
        open: false,
        trigger: null,
        previousOverflow: '',
        media: null,
        onDesktop: null,
        init() {
            this.media = window.matchMedia('(min-width: 1024px)');
            this.onDesktop = () => { if (this.media.matches) this.close(false); };
            this.media.addEventListener('change', this.onDesktop);
            this.$watch('open', value => {
                if (!lockScroll) return;
                if (value) {
                    this.previousOverflow = document.body.style.overflow;
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = this.previousOverflow;
                }
            });
        },
        toggle(event) {
            if (this.open) return this.close();
            this.trigger = event.currentTarget;
            this.open = true;
            this.$nextTick(() => requestAnimationFrame(() => requestAnimationFrame(() => {
                if (this.open) this.$refs.panel?.querySelector('button, a[href], summary, input, select, textarea')?.focus();
            })));
        },
        close(restoreFocus = true) {
            if (!this.open) return;
            this.open = false;
            if (restoreFocus) this.$nextTick(() => this.trigger?.focus());
        },
        trap(event) {
            if (!this.open || event.key !== 'Tab') return;
            const nodes = [...this.$refs.panel.querySelectorAll('a[href], button, summary, input, select, textarea, [tabindex="0"]')]
                .filter(node => node.getClientRects().length && !node.disabled);
            const first = nodes[0], last = nodes[nodes.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
        },
        destroy() {
            this.media?.removeEventListener('change', this.onDesktop);
            if (this.open && lockScroll) document.body.style.overflow = this.previousOverflow;
        },
    };
}
