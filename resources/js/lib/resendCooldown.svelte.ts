import { onDestroy } from 'svelte';

type CooldownState = {
    secondsLeft: number;
    targetTimestamp: number | null;
};

export function createResendCooldown() {
    const state = $state<CooldownState>({
        secondsLeft: 0,
        targetTimestamp: null,
    });

    let timer: ReturnType<typeof setInterval> | null = null;

    function stop(): void {
        if (timer) {
            clearInterval(timer);
            timer = null;
        }

        state.targetTimestamp = null;
        state.secondsLeft = 0;
    }

    function update(): number {
        if (!state.targetTimestamp) {
            state.secondsLeft = 0;

            return 0;
        }

        const remaining = Math.max(
            0,
            Math.ceil((state.targetTimestamp - Date.now()) / 1000),
        );
        state.secondsLeft = remaining;

        if (remaining <= 0) {
            stop();
        }

        return remaining;
    }

    function start(duration: number): void {
        if (duration <= 0) {
            stop();

            return;
        }

        if (timer) {
            clearInterval(timer);
        }

        state.targetTimestamp = Date.now() + duration * 1000;
        state.secondsLeft = duration;
        timer = setInterval(update, 1000);
    }

    function syncFromServer(timestamp: number | null): void {
        if (!timestamp) {
            stop();

            return;
        }

        if (timer) {
            clearInterval(timer);
        }

        state.targetTimestamp = timestamp * 1000;
        update();

        if (state.secondsLeft > 0) {
            timer = setInterval(update, 1000);
        }
    }

    onDestroy(stop);

    return {
        state,
        start,
        stop,
        update,
        syncFromServer,
    };
}
