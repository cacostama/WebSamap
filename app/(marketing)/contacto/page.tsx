import type { Metadata } from 'next'
import Image from 'next/image'
import { Clock3, Mail, MapPin, MessageCircle, Phone } from 'lucide-react'

import ctaImage from '@/assets/images/call_to_action.png'
import supportImage from '@/assets/images/team.png'
import { Button } from '@/components/ui/button'
import { Navbar } from '@/components/marketing/navbar'
import { Footer } from '@/components/marketing/footer'

export const metadata: Metadata = {
  title: 'Contacto | SAMAP - Cotizá tu Plan de Salud',
  description: 'Contactá a SAMAP para cotizar tu plan de salud. Atención personalizada, WhatsApp y central telefónica.',
}

export default function ContactoPage() {
  return (
    <div className="min-h-screen">
      <Navbar />
      
      <section className="bg-brand-blue-5 px-6 py-16">
        <div className="mx-auto grid max-w-6xl gap-10 lg:grid-cols-[1fr_0.9fr] lg:items-center">
          <div>
            <p className="text-sm uppercase tracking-[0.24em] text-brand-blue">Contacto</p>
            <h1 className="mt-4 text-4xl md:text-5xl text-brand-blue" style={{ fontFamily: 'DM Serif Display, serif' }}>
              Estamos listos para orientarte y ayudarte a cotizar.
            </h1>
            <p className="mt-4 max-w-2xl text-lg text-neutral-600">
              Elegi el canal que te resulte mas comodo. La maqueta prioriza claridad de contacto, ubicacion y confianza institucional.
            </p>
          </div>
          <div className="overflow-hidden rounded-[2rem] bg-white p-3 shadow-[0_24px_60px_rgba(39,71,103,0.12)]">
            <Image src={supportImage} alt="Equipo de atencion de SAMAP" className="h-auto w-full rounded-[1.5rem] object-cover" priority />
          </div>
        </div>
      </section>

      <section className="py-20 px-6">
        <div className="max-w-4xl mx-auto grid md:grid-cols-2 gap-8">
          <div className="p-8 rounded-lg border border-neutral-200 bg-white">
            <h3 className="text-xl text-brand-blue-dark mb-4" style={{ fontFamily: 'DM Serif Display, serif' }}>
              Teléfono
            </h3>
            <p className="flex items-center gap-3 text-2xl font-semibold text-brand-blue"><Phone className="h-5 w-5" />+595 21 219 6000</p>
            <p className="mt-2 text-neutral-500">Lun - Vie: 7:00 - 19:00</p>
            <p className="text-neutral-500">Sábados: 7:00 - 12:00</p>
          </div>
          
          <div className="p-8 rounded-lg border border-neutral-200 bg-white">
            <h3 className="text-xl text-brand-blue-dark mb-4" style={{ fontFamily: 'DM Serif Display, serif' }}>
              WhatsApp
            </h3>
            <a 
              href="https://wa.me/595982932062" 
              target="_blank" 
              rel="noopener noreferrer"
              className="flex items-center gap-3 text-2xl font-semibold text-brand-blue hover:underline"
            >
              <MessageCircle className="h-5 w-5" />
              +595 982 932 062
            </a>
            <p className="mt-2 text-neutral-500">Respuesta inmediata</p>
          </div>
          
          <div className="p-8 rounded-lg border border-neutral-200 bg-white">
            <h3 className="text-xl text-brand-blue-dark mb-4" style={{ fontFamily: 'DM Serif Display, serif' }}>
              Email
            </h3>
            <a 
              href="mailto:info@samap.com.py"
              className="flex items-center gap-3 text-xl font-semibold text-brand-blue hover:underline"
            >
              <Mail className="h-5 w-5" />
              info@samap.com.py
            </a>
            <p className="mt-2 text-neutral-500">Respondemos en 24h</p>
          </div>
          
          <div className="p-8 rounded-lg border border-neutral-200 bg-white">
            <h3 className="text-xl text-brand-blue-dark mb-4" style={{ fontFamily: 'DM Serif Display, serif' }}>
              Sede Central
            </h3>
            <p className="flex items-center gap-3 text-lg font-medium text-brand-blue"><MapPin className="h-5 w-5" />Pettirossi 380</p>
            <p className="text-neutral-500">c/ Pai Perez, Asunción</p>
            <p className="mt-2 flex items-center gap-3 text-neutral-500"><Clock3 className="h-4 w-4" />Horario: 7:00 - 19:00</p>
          </div>
        </div>
      </section>

      <section className="py-20 px-6 bg-neutral-50">
        <div className="max-w-2xl mx-auto">
          <h2 className="text-2xl text-brand-blue-dark text-center mb-8" style={{ fontFamily: 'DM Serif Display, serif' }}>
            Envíanos un mensaje
          </h2>
          <form className="space-y-6">
            <div>
              <label htmlFor="nombre" className="block text-sm font-medium text-neutral-700 mb-2">
                Nombre completo
              </label>
              <input
                type="text"
                id="nombre"
                name="nombre"
                className="w-full px-4 py-3 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-brand-blue focus:border-transparent outline-none"
                placeholder="Tu nombre"
              />
            </div>
            <div>
              <label htmlFor="telefono" className="block text-sm font-medium text-neutral-700 mb-2">
                Teléfono
              </label>
              <input
                type="tel"
                id="telefono"
                name="telefono"
                className="w-full px-4 py-3 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-brand-blue focus:border-transparent outline-none"
                placeholder="+595 9XX XXX XXX"
              />
            </div>
            <div>
              <label htmlFor="email" className="block text-sm font-medium text-neutral-700 mb-2">
                Email
              </label>
              <input
                type="email"
                id="email"
                name="email"
                className="w-full px-4 py-3 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-brand-blue focus:border-transparent outline-none"
                placeholder="tu@email.com"
              />
            </div>
            <div>
              <label htmlFor="mensaje" className="block text-sm font-medium text-neutral-700 mb-2">
                Mensaje
              </label>
              <textarea
                id="mensaje"
                name="mensaje"
                rows={4}
                className="w-full px-4 py-3 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-brand-blue focus:border-transparent outline-none"
                placeholder="¿En qué podemos ayudarte?"
              />
            </div>
            <p className="text-sm text-neutral-500">
              Esta es una maqueta visual. El formulario todavia no envia datos.
            </p>
            <Button type="button" className="w-full bg-brand-blue hover:bg-brand-blue-dark">
              Enviar mensaje
            </Button>
          </form>
        </div>
      </section>

      <section className="px-6 pb-20">
        <div className="mx-auto grid max-w-6xl gap-8 overflow-hidden rounded-[2rem] bg-brand-blue text-white lg:grid-cols-[1fr_0.9fr] lg:items-center">
          <div className="p-10 md:p-12">
            <p className="text-sm uppercase tracking-[0.24em] text-white/70">Asesoria rapida</p>
            <h2 className="mt-3 text-3xl" style={{ fontFamily: 'DM Serif Display, serif' }}>
              Si preferis, tambien podemos comenzar la conversacion por WhatsApp.
            </h2>
            <p className="mt-4 max-w-xl text-white/80">
              Este bloque funciona como cierre de pagina para reforzar la accion principal de contacto comercial.
            </p>
            <Button asChild className="mt-8 bg-white text-brand-blue hover:bg-white/90">
              <a href="https://wa.me/595982932062" target="_blank" rel="noopener noreferrer">Escribirme por WhatsApp</a>
            </Button>
          </div>
          <div className="h-full min-h-[300px]">
            <Image src={ctaImage} alt="Canal de contacto comercial" className="h-full w-full object-cover" />
          </div>
        </div>
      </section>

      <Footer />
    </div>
  )
}
