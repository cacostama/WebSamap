import Link from 'next/link'

import { Footer } from '@/components/marketing/footer'
import { Navbar } from '@/components/marketing/navbar'

export default function PrivacidadPage() {
  return (
    <div className="min-h-screen">
      <Navbar />

      <section className="bg-brand-blue-5 px-6 py-16">
        <div className="mx-auto max-w-6xl">
          <p className="text-sm uppercase tracking-[0.24em] text-brand-blue">Legal</p>
          <h1 className="mt-4 text-4xl md:text-5xl text-brand-blue-dark">
            Política de Privacidad
          </h1>
        </div>
      </section>

      <section className="py-20 px-6">
        <div className="mx-auto max-w-3xl">
          <p className="text-neutral-600 mb-8">
            Última actualización: Enero 2025
          </p>

          <h2 className="text-2xl text-brand-blue-dark mb-4">
            1. Responsable del Tratamiento
          </h2>
          <p className="text-neutral-600 mb-8">
            SAMAP S.A., con domicilio en Pettirossi 380 c/ Pai Perez, Asunción, Paraguay, es responsable del tratamiento de sus datos personales conforme a la Ley 6534/20 de Protección de Datos Personales del Paraguay.
          </p>

          <h2 className="text-2xl text-brand-blue-dark mb-4">
            2. Datos que Recopilamos
          </h2>
          <p className="text-neutral-600 mb-4">
            Recopilamos los siguientes datos personales:
          </p>
          <ul className="list-disc list-inside text-neutral-600 mb-8 space-y-2">
            <li>Nombre completo</li>
            <li>Correo electrónico</li>
            <li>Número de teléfono</li>
            <li>Información de contacto voluntaria</li>
          </ul>

          <h2 className="text-2xl text-brand-blue-dark mb-4">
            3. Finalidad del Tratamiento
          </h2>
          <p className="text-neutral-600 mb-8">
            Sus datos son utilizados exclusivamente para: gestionar solicitudes de información sobre planes de salud, contactarlo con fines comerciales, y mejorar nuestros servicios. No compartimos sus datos con terceros sin su consentimiento expreso.
          </p>

          <h2 className="text-2xl text-brand-blue-dark mb-4">
            4. Sus Derechos
          </h2>
          <p className="text-neutral-600 mb-4">
            Usted tiene derecho a:
          </p>
          <ul className="list-disc list-inside text-neutral-600 mb-8 space-y-2">
            <li>Acceder a sus datos personales</li>
            <li>Rectificar datos inexactos</li>
            <li>Solicitar la eliminación de sus datos</li>
            <li>Oponerse al tratamiento</li>
          </ul>

          <h2 className="text-2xl text-brand-blue-dark mb-4">
            5. Contacto
          </h2>
          <p className="text-neutral-600 mb-8">
            Para ejercer sus derechos o consultas sobre esta política, contáctenos en: <a href="mailto:info@samap.com.py" className="text-brand-blue hover:underline">info@samap.com.py</a>
          </p>

          <div className="mt-12 pt-8 border-t">
            <Link href="/" className="text-brand-blue hover:underline">
              ← Volver al inicio
            </Link>
          </div>
        </div>
      </section>

      <Footer />
    </div>
  )
}