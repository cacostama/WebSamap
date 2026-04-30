import type { Metadata, Viewport } from 'next'
import Script from 'next/script'
import './globals.css'
import {
  EMPRESA_NOMBRE,
  EMPRESA_ANIOS_TRAYECTORIA,
  EMPRESA_SOCIOS_APROX,
  TELEFONO,
  EMAIL,
  REDES_SOCIALES,
  LOCALE,
} from '@/lib/constants'

export const viewport: Viewport = {
  width: 'device-width',
  initialScale: 1,
  maximumScale: 5,
}

const BASE_URL = process.env.PUBLIC_URL ?? 'https://samap.com.py'

export const metadata: Metadata = {
  metadataBase: new URL(BASE_URL),
  title: {
    default: `${EMPRESA_NOMBRE} - Medicina Prepaga del Sanatorio Adventista`,
    template: `%s | ${EMPRESA_NOMBRE}`,
  },
  description: `Planes de salud con más de ${EMPRESA_ANIOS_TRAYECTORIA} años de trayectoria. Cobertura médica integral para vos y tu familia. Más de ${EMPRESA_SOCIOS_APROX.toLocaleString('es-PY')} socios confían en nosotros.`,
  keywords: [
    'medicina prepaga Paraguay',
    'planes de salud',
    'sanatorio adventista',
    'cobertura médica',
    'SAMAP',
    'asunción',
    'obra social',
    'prepaga',
  ],
  authors: [{ name: EMPRESA_NOMBRE }],
  creator: EMPRESA_NOMBRE,
  publisher: EMPRESA_NOMBRE,
  formatDetection: {
    email: false,
    telephone: false,
  },
  openGraph: {
    type: 'website',
    locale: LOCALE,
    url: BASE_URL,
    siteName: EMPRESA_NOMBRE,
    title: `${EMPRESA_NOMBRE} - Medicina Prepaga del Sanatorio Adventista`,
    description: `Planes de salud con más de ${EMPRESA_ANIOS_TRAYECTORIA} años de trayectoria. Cobertura médica integral para vos y tu familia.`,
    images: [
      {
        url: '/og-image.png',
        width: 1200,
        height: 630,
        alt: `${EMPRESA_NOMBRE} - Medicina Prepaga`,
      },
    ],
  },
  twitter: {
    card: 'summary_large_image',
    title: `${EMPRESA_NOMBRE} - Medicina Prepaga`,
    description: `Planes de salud con más de ${EMPRESA_ANIOS_TRAYECTORIA} años de trayectoria.`,
    images: ['/og-image.png'],
  },
  robots: {
    index: true,
    follow: true,
    googleBot: {
      index: true,
      follow: true,
      'max-video-preview': -1,
      'max-image-preview': 'large',
      'max-snippet': -1,
    },
  },
  alternates: {
    canonical: BASE_URL,
    languages: {
      'es-PY': BASE_URL,
    },
  },
}

const organizationSchema = {
  '@context': 'https://schema.org',
  '@type': 'Organization',
  name: EMPRESA_NOMBRE,
  url: BASE_URL,
  logo: `${BASE_URL}/logo.png`,
  description: `Medicina prepaga con más de ${EMPRESA_ANIOS_TRAYECTORIA} años de trayectoria, atendiendo a más de ${EMPRESA_SOCIOS_APROX} socios.`,
  foundingDate: new Date().getFullYear() - EMPRESA_ANIOS_TRAYECTORIA,
  slogan: 'Tu salud, nuestra prioridad',
  sameAs: Object.values(REDES_SOCIALES),
  contactPoint: {
    '@type': 'ContactPoint',
    telephone: TELEFONO,
    contactType: 'customer service',
    availableLanguage: ['Spanish', 'Guarani'],
    areaServed: 'PY',
  },
}

const localBusinessSchema = {
  '@context': 'https://schema.org',
  '@type': 'MedicalBusiness',
  name: EMPRESA_NOMBRE,
  image: `${BASE_URL}/og-image.png`,
  url: BASE_URL,
  telephone: TELEFONO,
  email: EMAIL,
  address: {
    '@type': 'PostalAddress',
    streetAddress: 'Pettirossi 380',
    addressLocality: 'Asunción',
    addressCountry: 'PY',
    postalCode: '',
  },
  openingHoursSpecification: [
    {
      '@type': 'OpeningHoursSpecification',
      dayOfWeek: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
      opens: '07:00',
      closes: '19:00',
    },
  ],
  geo: {
    '@type': 'GeoCoordinates',
    latitude: -25.2637,
    longitude: -57.5759,
  },
  areaServed: {
    '@type': 'Country',
    name: 'Paraguay',
  },
  medicalSpecialty: 'GeneralMedicine',
  hasOfferCatalog: {
    '@type': 'OfferCatalog',
    name: 'Planes de Salud SAMAP',
    itemListElement: [
      { '@type': 'Offer', name: 'Plan Alfa' },
      { '@type': 'Offer', name: 'Plan Beta' },
      { '@type': 'Offer', name: 'Plan Gamma' },
    ],
  },
}

export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html lang="es">
      <body>
        <Script
          id="schema-org"
          type="application/ld+json"
          strategy="beforeInteractive"
          dangerouslySetInnerHTML={{
            __html: JSON.stringify([organizationSchema, localBusinessSchema]),
          }}
        />
        {children}
      </body>
    </html>
  )
}