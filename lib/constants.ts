export const WHATSAPP_COMERCIAL = process.env.WHATSAPP_COMERCIAL ?? ''
export const WHATSAPP_URL = WHATSAPP_COMERCIAL
  ? `https://api.whatsapp.com/send?phone=${WHATSAPP_COMERCIAL}`
  : ''
export const WHATSAPP_DISPLAY = WHATSAPP_COMERCIAL
  ? WHATSAPP_COMERCIAL.replace(/(\d{3})(\d{3})(\d{3})/, '$1 $2 $3')
  : ''

export const TIMEZONE = 'America/Asuncion'
export const LOCALE = 'es-PY'
export const CURRENCY = 'PYG'

export const EMPRESA_NOMBRE = 'SAMAP S.A.'
export const EMPRESA_ANIOS_TRAYECTORIA = 35
export const EMPRESA_SOCIOS_APROX = 8000

export const TELEFONO = '+595 21 219 6000'
export const DIRECCION = 'Pettirossi 380 c/ Pai Perez, Asunción'
export const EMAIL = 'info@samap.com.py'

export const REDES_SOCIALES = {
  facebook: 'https://facebook.com/samappy',
  instagram: 'https://instagram.com/samappy',
}

export const FACEBOOK_URL = REDES_SOCIALES.facebook
export const INSTAGRAM_URL = REDES_SOCIALES.instagram

export const PLANES = [
  { slug: 'alfa', nombre: 'Alfa' },
  { slug: 'beta', nombre: 'Beta' },
  { slug: 'gamma', nombre: 'Gamma' },
] as const