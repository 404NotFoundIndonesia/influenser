import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export const formatBytes = (bytes: number): string => {
    if (bytes === 0 || bytes === null || bytes === undefined) return '0 B';

    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    const size = parseFloat((bytes / Math.pow(k, i)).toFixed(2));
    return `${size} ${sizes[i]}`;
};

export const estimatedFormatBytes = (bytes: number): string => {
    if (bytes === 0 || bytes === null || bytes === undefined) return '0 B';

    const k = 1000;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    const size = parseFloat((bytes / Math.pow(k, i)).toFixed(2));
    return `${size} ${sizes[i]}`;
};

export const dateHumanFormat = (value: Date | string | null): string => {
    if (!value)
        return '-';

    const date = new Date(value);
    return date.toLocaleDateString('en-GB', {
        weekday: 'short', day: '2-digit',
        month: 'short', year: 'numeric',
    });
};

export const dateHumanFormatWithTime = (value: Date | string | null) => {
    if (!value)
        return '-';

    const date = new Date(value);
    return date.toLocaleDateString('en-GB', {
        weekday: 'short',
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    }) + ' WIB';
};

export const digitFormatter = (value: number): string => new Intl.NumberFormat('id-ID').format(value);

export const shortNumberFormatter = (value: number): string => {
    if (value >= 1_000_000_000_000) {
        return (value / 1_000_000_000_000).toFixed(1).replace(/\.0$/, '') + 'T';
    } else if (value >= 1_000_000_000) {
        return (value / 1_000_000_000).toFixed(1).replace(/\.0$/, '') + 'B';
    } else if (value >= 1_000_000) {
        return (value / 1_000_000).toFixed(1).replace(/\.0$/, '') + 'M';
    } else if (value >= 1_000) {
        return (value / 1_000).toFixed(1).replace(/\.0$/, '') + 'k';
    }

    return new Intl.NumberFormat('id-ID').format(value);
};
