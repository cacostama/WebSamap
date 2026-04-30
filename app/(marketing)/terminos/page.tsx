import Link from 'next/link'

import { Footer } from '@/components/marketing/footer'
import { Navbar } from '@/components/marketing/navbar'

export default function TerminosPage() {
  return (
    <div className="min-h-screen">
      <Navbar />

      <section className="bg-brand-blue-5 px-6 py-16">
        <div className="mx-auto max-w-6xl">
          <p className="text-sm uppercase tracking-[0.24em] text-brand-blue">Legal</p>
          <h1 className="mt-4 text-4xl md:text-5xl text-brand-blue-dark">
            Términos y Condiciones
          </h1>
        </div>
      </section>

      <section className="py-20 px-6">
        <div className="mx-auto max-w-3xl">
          <p className="text-neutral-600 mb-8">
            Última actualización: Enero 2025
          </p>

          <h2 className="text-2xl text-brand-blue-dark mb-4">
            1. Aceptación de los Términos
          </h2>
          <p className="text-neutral-600 mb-8">
            Al acceder y utilizar este sitio web de SAMAP S.A., usted acepta cumplir con estos términos y condiciones en su totalidad. Si no está de acuerdo con alguna parte de estos términos, le solicitamos no utilizar nuestro sitio.
          </p>

          <h2 className="text-2xl text-brand-blue-dark mb-4">
            2. Servicio de Medicina Prepaga
          </h2>
          <p className="text-neutral-600 mb-4">
            SAMAP S.A. es una empresa de medicina prepaga regulada por la Superintendencia de Salud del Paraguay. Nuestros servicios incluyen:
          </p>
          <ul className="list-disc list-inside text-neutral-600 mb-8 space-y-2">
            <li>Planes de salud con cobertura médico-hospitalaria</li>
            <li>Atención en red de prestadores médicos</li>
            <li>Coberturas según el plan seleccionado</li>
          </ul>

          <h2 className="text-2xl text-brand-blue-dark mb-4">
            3. Información del Sitio
          </h2>
          <p className="text-neutral-600 mb-8">
            La información presentada en este sitio web es de carácter orientativo. Las coberturas, precios y condiciones de los planes pueden variar. Para información contractual vigente, consulte la documentación oficial de su plan.
          </p>

          <h2 className="text-2xl text-brand-blue-dark mb-4">
            4. Propiedad Intelectual
          </h2>
          <p className="text-neutral-600 mb-8">
            Todo el contenido de este sitio web, incluyendo textos, gráficos, logotipos e imágenes, es propiedad de SAMAP S.A. y está protegido por las leyes de propiedad intelectual del Paraguay.
          </p>

          <h2 className="text-2xl text-brand-blue-dark mb-4">
            5. Limitación de Responsabilidad
          </h2>
          <p className="text-neutral-600 mb-8">
            SAMAP S.A. no será responsable por daños directos o indirectos derivados del uso de este sitio web o de la información contenida en el mismo.
          </p>

          <h2 className="text-2xl text-brand-blue-dark mb-4">
            6. Modificaciones
          </h2>
          <p className="text-neutral-600 mb-8">
            SAMAP S.A. se reserva el derecho de modificar estos términos y condiciones en cualquier momento. Las modificaciones entrarán en vigor desde su publicación en el sitio.
          </p>

          <h2 className="text-2xl text-brand-blue-dark mb-4">
            7. Ley Aplicable
          </h2>
          <p className="text-neutral-600 mb-8">
            Estos términos se rigen por las leyes de la República del Paraguay.
          </p>

          <h2 className="text-2xl text-brand-blue-dark mb-4">
            8. Contacto
          </h2>
          <p className="text-neutral-600 mb-8">
            Para consultas sobre estos términos, contáctenos en: <a href="mailto:info@samap.com.py" className="text-brand-blue hover:underline">info@samap.com.py</a>
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