export function useThemeColors() {
    const themeMap = {
        accion: {
            cardBg: 'linear-gradient(145deg, #FEF08A 0%, #FDE047 50%, #FEF9C3 100%)',
            border: '#EAB308',
            borderHover: '#CA8A04',
            badgeBg: 'linear-gradient(135deg, #F59E0B 0%, #D97706 100%)',
            badgeText: '#FFFFFF',
            btnBg: '#FEF08A',
            btnText: '#78350F',
            btnBorder: '#CA8A04',
            shadow: '0 6px 18px rgba(234, 179, 8, 0.18)',
            title: 'Acción de Sensibilización',
            icon: 'Megaphone',
        },
        difusion: {
            cardBg: 'linear-gradient(145deg, #E0E7FF 0%, #C7D2FE 50%, #EEF2FF 100%)',
            border: '#818CF8',
            borderHover: '#4F46E5',
            badgeBg: 'linear-gradient(135deg, #6366F1 0%, #4F46E5 100%)',
            badgeText: '#FFFFFF',
            btnBg: '#E0E7FF',
            btnText: '#312E81',
            btnBorder: '#6366F1',
            shadow: '0 6px 18px rgba(99, 102, 241, 0.18)',
            title: 'Acción de Difusión',
            icon: 'Radio',
        },
        sector: {
            cardBg: 'linear-gradient(145deg, #BAE6FD 0%, #7DD3FC 50%, #E0F2FE 100%)',
            border: '#38BDF8',
            borderHover: '#0284C7',
            badgeBg: 'linear-gradient(135deg, #0EA5E9 0%, #0284C7 100%)',
            badgeText: '#FFFFFF',
            btnBg: '#BAE6FD',
            btnText: '#0369A1',
            btnBorder: '#0284C7',
            shadow: '0 6px 18px rgba(14, 165, 233, 0.18)',
            title: 'Sectores del Aula',
            icon: 'LayoutGrid',
        },
        evidencia: {
            cardBg: 'linear-gradient(145deg, #FECDD3 0%, #FDA4AF 50%, #FFE4E6 100%)',
            border: '#FB7185',
            borderHover: '#E11D48',
            badgeBg: 'linear-gradient(135deg, #F43F5E 0%, #BE123C 100%)',
            badgeText: '#FFFFFF',
            btnBg: '#FECDD3',
            btnText: '#881337',
            btnBorder: '#E11D48',
            shadow: '0 6px 18px rgba(244, 63, 94, 0.18)',
            title: 'Asistencia Técnica',
            icon: 'FileText',
        },
        informe: {
            cardBg: 'linear-gradient(145deg, #FED7AA 0%, #FDBA74 50%, #FFEDD5 100%)',
            border: '#FB923C',
            borderHover: '#EA580C',
            badgeBg: 'linear-gradient(135deg, #F97316 0%, #C2410C 100%)',
            badgeText: '#FFFFFF',
            btnBg: '#FED7AA',
            btnText: '#7C2D12',
            btnBorder: '#EA580C',
            shadow: '0 6px 18px rgba(249, 115, 22, 0.18)',
            title: 'Biblioteca del Aula',
            icon: 'BookOpen',
        },
        plan: {
            cardBg: 'linear-gradient(145deg, #A5F3FC 0%, #67E8F9 50%, #CFFAFE 100%)',
            border: '#22D3EE',
            borderHover: '#0891B2',
            badgeBg: 'linear-gradient(135deg, #06B6D4 0%, #0E7490 100%)',
            badgeText: '#FFFFFF',
            btnBg: '#A5F3FC',
            btnText: '#164E63',
            btnBorder: '#0891B2',
            shadow: '0 6px 18px rgba(6, 182, 212, 0.18)',
            title: 'Espacio de Lectura en el Hogar',
            icon: 'BookHeart',
        },
        produccion: {
            cardBg: 'linear-gradient(145deg, #BBF7D0 0%, #86EFAC 50%, #DCFCE7 100%)',
            border: '#4ADE80',
            borderHover: '#16A34A',
            badgeBg: 'linear-gradient(135deg, #10B981 0%, #047857 100%)',
            badgeText: '#FFFFFF',
            btnBg: '#BBF7D0',
            btnText: '#14532D',
            btnBorder: '#16A34A',
            shadow: '0 6px 18px rgba(16, 185, 129, 0.18)',
            title: 'Producción de Textos Infantiles',
            icon: 'NotebookPen',
        },
        agenda: {
            cardBg: 'linear-gradient(145deg, #E9D5FF 0%, #D8B4FE 50%, #F3E8FF 100%)',
            border: '#C084FC',
            borderHover: '#9333EA',
            badgeBg: 'linear-gradient(135deg, #A855F7 0%, #7E22CE 100%)',
            badgeText: '#FFFFFF',
            btnBg: '#E9D5FF',
            btnText: '#581C87',
            btnBorder: '#9333EA',
            shadow: '0 6px 18px rgba(168, 85, 247, 0.18)',
            title: 'Agenda de Lectura',
            icon: 'CalendarCheck',
        },
    };

    const statsConfig = {
        agenda:     { short: 'Agenda',     icon: 'CalendarCheck', circleBg: '#FCE7F3', iconColor: '#EC4899', pctColor: '#DB2777', barColor: '#EC4899', full: 'Agenda de Lectura' },
        accion:     { short: 'Acciones',   icon: 'Megaphone',     circleBg: '#FEF3C7', iconColor: '#F59E0B', pctColor: '#D97706', barColor: '#F59E0B', full: 'Acciones de Sensibilización' },
        difusion:   { short: 'Difusión',   icon: 'Radio',         circleBg: '#EDE9FE', iconColor: '#8B5CF6', pctColor: '#7C3AED', barColor: '#8B5CF6', full: 'Acciones de Difusión' },
        evidencia:  { short: 'Evidencias', icon: 'FileText',      circleBg: '#FFE4E6', iconColor: '#F43F5E', pctColor: '#E11D48', barColor: '#F43F5E', full: 'Evidencias de Asistencia Técnica' },
        plan:       { short: 'Planes',     icon: 'BookHeart',     circleBg: '#CFFAFE', iconColor: '#06B6D4', pctColor: '#0891B2', barColor: '#06B6D4', full: 'Espacio de Lectura en el Hogar' },
        produccion: { short: 'Producción', icon: 'NotebookPen',   circleBg: '#DCFCE7', iconColor: '#10B981', pctColor: '#16A34A', barColor: '#10B981', full: 'Producción de Textos Infantiles' },
        informe:    { short: 'Informes',   icon: 'BookOpen',      circleBg: '#FFEDD5', iconColor: '#F97316', pctColor: '#EA580C', barColor: '#F97316', full: 'Biblioteca del Aula (Informes)' },
    };

    function resolveTheme(modulo) {
        const href = (modulo.href || '').toLowerCase();
        const text = (modulo.text || '').toLowerCase();

        for (const [key, theme] of Object.entries(themeMap)) {
            if (href.includes(key) || text.includes(key)) {
                return theme;
            }
        }

        return {
            cardBg: 'linear-gradient(150deg, #E2E8F0 0%, #F1F5F9 100%)',
            border: '#CBD5E1',
            borderHover: '#64748B',
            badgeBg: 'linear-gradient(135deg, #64748B 0%, #475569 100%)',
            badgeText: '#FFFFFF',
            btnBg: '#E2E8F0',
            btnText: '#1E293B',
            btnBorder: '#CBD5E1',
            shadow: '0 6px 18px rgba(0, 0, 0, 0.05)',
            title: modulo.text,
            icon: 'Folder',
        };
    }

    return {
        themeMap,
        statsConfig,
        resolveTheme,
    };
}
