export type SessionItem = {
    id: string;
    ip_address: string;
    is_current_device: boolean;
    agent: {
        device_type: 'desktop' | 'mobile' | 'tablet' | 'bot';
        platform: string;
        browser: string;
        display_name: string;
    };
    last_active: string;
    last_active_at: string;
};
