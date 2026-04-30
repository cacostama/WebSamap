import Image from 'next/image'

import aboutImage from '@/assets/images/about_img.png'
import heroImage from '@/assets/images/hero_slider.png'
import reviewOne from '@/assets/images/review.png'
import reviewTwo from '@/assets/images/review2.png'
import reviewThree from '@/assets/images/review3.png'

import { Footer } from '@/components/marketing/footer'
import { Button } from '@/components/ui/button'
import { Navbar } from '@/components/marketing/navbar'
import { PlansCarousel } from '@/components/marketing/PlansCarousel'
import { WHATSAPP_URL } from '@/lib/constants'

export default function HomePage() {
  return (
    <div className="min-h-screen">
      <Navbar />
      
      {/* Hero Section */}
      <section className="py-20 md:py-28 px-6 bg-gradient-to-b from-white to-brand-blue-5">
        <div className="max-w-6xl mx-auto grid gap-12 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
          <div className="max-w-3xl">
            <h1 className="text-4xl md:text-5xl lg:text-6xl text-brand-blue leading-tight">
              Tu salud y la de tu familia, en las mejores manos
            </h1>
            <p className="mt-6 text-lg text-neutral-600 max-w-xl">
              Más de 35 años cuidando a las familias paraguayas con el respaldo institucional del Sanatorio Adventista de Asunción.
            </p>
            <div className="mt-8 flex flex-wrap gap-4">
              <Button asChild size="lg" className="bg-brand-blue hover:bg-brand-blue-dark">
                <a href="#planes">Cotizar mi plan</a>
              </Button>
              <Button asChild variant="outline" size="lg">
                <a href="#planes">Ver planes</a>
              </Button>
            </div>
          </div>
          <div className="relative hidden lg:block">
            <div className="absolute -left-6 top-8 h-24 w-24 rounded-full bg-brand-green/25 blur-2xl" />
            <div className="absolute -right-6 bottom-8 h-28 w-28 rounded-full bg-brand-blue/10 blur-2xl" />
            <div className="relative overflow-hidden rounded-[2rem] border border-white/80 bg-white p-4 shadow-[0_28px_80px_rgba(39,71,103,0.16)]">
              <Image
                src={heroImage}
                alt="Familia recibiendo atención médica"
                className="h-auto w-full rounded-[1.5rem] object-cover"
                priority
                sizes="(max-width: 1024px) 100vw, 50vw"
              />
            </div>
          </div>
        </div>
      </section>

      {/* Trust Stats */}
      <section className="py-12 border-y border-neutral-100">
        <div className="max-w-6xl mx-auto px-6 grid grid-cols-2 md:grid-cols-4 gap-8">
          {[
            { value: '35+', label: 'Años de experiencia' },
            { value: '8.000+', label: 'Socios activos' },
            { value: '24/7', label: 'Atención de urgencias' },
            { value: '100+', label: 'Prestadores médicos' },
          ].map((stat) => (
            <div key={stat.label} className="text-center">
              <div className="text-3xl md:text-4xl font-semibold text-brand-blue">
                {stat.value}
              </div>
              <div className="mt-1 text-sm text-neutral-500">{stat.label}</div>
            </div>
          ))}
        </div>
      </section>

      {/* Sección Nosotros */}
      <section id="nosotros" className="py-20 px-6 bg-white">
        <div className="max-w-6xl mx-auto">
          <div className="text-center mb-16">
            <h2 className="text-4xl md:text-5xl text-brand-blue-dark mb-4">SAMAP</h2>
            <p className="text-xl text-brand-green font-medium">
              Más de 43 años cuidando tu salud con compromiso y calidez.
            </p>
          </div>

          <div className="max-w-3xl mx-auto text-neutral-600 space-y-6 text-base leading-relaxed">
            <p>
              SAMAP es medicina prepaga del Sanatorio Adventista de Asunción, y desde hace más de tres décadas acompañamos a miles de familias con un servicio médico confiable, humano y accesible.
            </p>
            <p>
              Nuestra historia comenzó con el propósito de brindar una cobertura de salud basada en la excelencia médica y los valores cristianos. Con el paso del tiempo, fuimos creciendo y adaptándonos a los nuevos desafíos del sector, sin perder de vista lo más importante: el cuidado integral de nuestros asegurados.
            </p>
            <p>
              Hoy, más de 8.000 personas confían en nosotros. Contamos con una red de prestadores de primer nivel en todo el país y un centro médico propio —el Sanatorio Adventista— con tecnología de vanguardia, atención personalizada y un enfoque que contempla el bienestar físico, mental y espiritual.
            </p>
            <p>
              En SAMAP, creemos que la salud se cuida todos los días, con empatía, responsabilidad y cercanía. Por eso, miramos al futuro con el mismo compromiso que nos impulsó desde el principio, dispuestos a seguir siendo tu mejor respaldo en salud.
            </p>
          </div>
        </div>
      </section>

      {/* Misión y Visión */}
      <section className="py-16 px-6 bg-neutral-50">
        <div className="max-w-6xl mx-auto grid md:grid-cols-2 gap-12">
          <div className="bg-white rounded-2xl p-8 border border-neutral-200">
            <h3 className="text-2xl text-brand-blue-dark mb-4">Nuestra Misión</h3>
            <p className="text-neutral-600 leading-relaxed">
              Ser una institución de salud autosustentable, dedicada a la atención y promoción de la salud física, mental, social y espiritual de nuestros asegurados, pacientes y comunidad, siguiendo el ejemplo del Señor Jesús, el Médico de los médicos.
            </p>
          </div>
          <div className="bg-white rounded-2xl p-8 border border-neutral-200">
            <h3 className="text-2xl text-brand-blue-dark mb-4">Nuestra Visión</h3>
            <p className="text-neutral-600 leading-relaxed">
              Ser una institución de salud con altos estándares de calidad en la prevención y tratamiento de la enfermedad, promoviendo la salud integral de nuestros asegurados, pacientes y comunidad.
            </p>
          </div>
        </div>
      </section>

      {/* Equipo Directivo */}
      <section className="py-20 px-6 bg-white">
        <div className="max-w-6xl mx-auto">
          <h2 className="text-3xl md:text-4xl text-brand-blue-dark text-center mb-12">
            Equipo Directivo
          </h2>
          <div className="grid md:grid-cols-3 gap-8">
            {[
              { nombre: 'Dr. Juan Carlos Martínez', cargo: 'Director General', imagen: 'team' },
              { nombre: 'Dra. María Elena González', cargo: 'Directora Médica', imagen: 'team2' },
              { nombre: 'Lic. Carlos Rodríguez', cargo: 'Director Comercial', imagen: 'team3' },
            ].map((person) => (
              <div key={person.nombre} className="text-center">
                <div className="w-40 h-40 mx-auto mb-4 rounded-full overflow-hidden bg-neutral-200 flex items-center justify-center" role="img" aria-label="Foto del equipo directivo SAMAP">
                  <span className="text-neutral-400 text-sm">👤</span>
                </div>
                <h3 className="text-lg font-medium text-brand-blue-dark">{person.nombre}</h3>
                <p className="text-sm text-neutral-500">{person.cargo}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Plans Carousel Section - NOW ANCHORED */}
      <PlansCarousel />

      {/* Benefits Section - NOW ANCHORED */}
      <section id="beneficios" className="py-20 px-6 bg-neutral-50">
        <div className="max-w-6xl mx-auto">
          <div className="grid md:grid-cols-2 gap-12 items-center">
            <div>
              <h2 className="text-3xl text-brand-blue-dark">
                ¿Por qué elegir SAMAP?
              </h2>
              <ul className="mt-6 space-y-4">
{[
                  'Consultas médicas en consultorio',
                  'Análisis de laboratorio',
                  'Urgencias y emergencias 24/7',
                  'Internación y cirugía',
                  'Medicamentos con descuento',
                  'Exámenes preventivos anuales',
                ].map((item) => (
                  <li key={item} className="flex items-center gap-3 text-neutral-700">
                    <svg aria-hidden="true" className="w-5 h-5 text-brand-green flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                    </svg>
                    {item}
                  </li>
                ))}
              </ul>
              <Button asChild variant="outline" className="mt-6">
                <a href="#planes">Ver todos los beneficios</a>
              </Button>
            </div>
            <div className="hidden md:block overflow-hidden rounded-[2rem] border border-white/70 bg-white p-3 shadow-[0_24px_60px_rgba(39,71,103,0.08)]">
              <Image
                src={aboutImage}
                alt="Equipo médico del Sanatorio Adventista"
                className="h-auto w-full rounded-[1.5rem] object-cover"
                sizes="(max-width: 768px) 100vw, 50vw"
              />
            </div>
          </div>
        </div>
      </section>

      {/* Prestadores Section - NOW ANCHORED */}
      <section id="prestadores" className="py-20 px-6">
        <div className="max-w-6xl mx-auto">
          <div className="text-center mb-12">
            <h2 className="text-3xl md:text-4xl text-brand-blue-dark">
              Red de Prestadores
            </h2>
            <p className="mt-3 text-neutral-600 max-w-lg mx-auto">
              Accedé a más de 100 profesionales y instituciones médicas en todo Paraguay.
            </p>
          </div>
          <div className="grid md:grid-cols-3 gap-6">
            {[
              { name: 'Sanatorio Adventista', specialty: 'Hospital central', city: 'Asunción' },
              { name: 'Clínica Yguazú', specialty: 'Consultas y urgencias', city: 'Ciudad del Este' },
              { name: 'Centro Médico Villeta', specialty: 'Especialidades', city: 'Villeta' },
            ].map((provider) => (
              <div key={provider.name} className="p-6 rounded-lg border border-neutral-200 bg-white">
                <h3 className="text-lg text-brand-blue-dark">{provider.name}</h3>
                <p className="mt-1 text-sm text-neutral-500">{provider.specialty}</p>
                <p className="mt-1 text-sm text-brand-blue">{provider.city}</p>
              </div>
            ))}
          </div>
          <div className="text-center mt-8">
            <Button asChild variant="outline">
              <a href="/prestadores">Ver todos los prestadores</a>
            </Button>
          </div>
        </div>
      </section>

      {/* Social Proof Section */}
      <section className="px-6 pb-20">
        <div className="mx-auto max-w-6xl rounded-[2rem] bg-brand-blue px-8 py-10 text-white md:px-12">
          <div className="grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
            <div>
              <p className="text-sm uppercase tracking-[0.24em] text-white/70">Confianza de nuestros socios</p>
              <h2 className="mt-3 text-3xl md:text-4xl">
                Una cobertura cercana se nota en cada experiencia.
              </h2>
            </div>
            <div className="grid gap-4 sm:grid-cols-3">
              {[
                { src: reviewOne, alt: 'Miembro afiliada a SAMAP' },
                { src: reviewTwo, alt: 'Afiliado a SAMAP' },
                { src: reviewThree, alt: 'Familia afiliada a SAMAP' },
              ].map((item) => (
                <div key={item.alt} className="overflow-hidden rounded-[1.5rem] bg-white/10 p-2">
                  <Image src={item.src} alt={item.alt} className="h-56 w-full rounded-[1rem] object-cover" sizes="(max-width: 640px) 100vw, (max-width: 1024px) 33vw, 33vw" />
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* Blog Section - NOW ANCHORED */}
      <section id="blog" className="py-20 px-6 bg-neutral-50">
        <div className="max-w-6xl mx-auto">
          <div className="text-center mb-12">
            <h2 className="text-3xl md:text-4xl text-brand-blue-dark">
              Mantenete informado
            </h2>
            <p className="mt-3 text-neutral-600 max-w-lg mx-auto">
              Enterate de las últimas novedades para cuidar tu salud y la de tu familia.
            </p>
          </div>
          <div className="grid md:grid-cols-3 gap-6">
            {[
              { title: 'Chequeos preventivos: por qué son importantes', date: '15 Enero 2025' },
              { title: 'Cómo elegir el plan de salud ideal', date: '8 Enero 2025' },
              { title: 'Tips para una vida más saludable', date: '2 Enero 2025' },
            ].map((post) => (
              <div key={post.title} className="p-6 rounded-lg border border-neutral-200 bg-white">
                <p className="text-sm text-neutral-500">{post.date}</p>
                <h3 className="mt-2 text-lg text-brand-blue-dark">{post.title}</h3>
                <a href="/blog" className="mt-4 text-sm text-brand-blue hover:underline block">Leer más artículos del blog →</a>
              </div>
            ))}
          </div>
          <div className="text-center mt-8">
            <Button asChild variant="outline">
              <a href="/blog">Ver todas las publicaciones</a>
            </Button>
          </div>
        </div>
      </section>

      {/* Bloques Destacados */}
      <section className="py-20 px-6">
        <div className="max-w-6xl mx-auto space-y-8">

          {/* Bloque 1: Tu vida */}
          <div className="bg-brand-blue-5 rounded-2xl p-8 md:p-12 text-center">
            <h2 className="text-2xl md:text-3xl text-brand-blue-dark mb-4">
              Tenemos un plan para cada etapa de TU VIDA.
            </h2>
            <Button asChild variant="default" className="bg-brand-blue hover:bg-brand-blue-dark">
              <a href="#planes">Conocé nuestros planes</a>
            </Button>
          </div>

          {/* Bloque 2: Assist Card */}
          <div className="bg-white rounded-2xl border border-neutral-200 p-8 md:p-12">
            <div className="grid md:grid-cols-2 gap-8 items-center">
              <div>
                <h2 className="text-2xl md:text-3xl text-brand-blue-dark mb-4">
                  Viajá más tranquilo
                </h2>
                <p className="text-neutral-600 mb-6">
                  Confirmá si tu plan incluye asistencia al viajero de Assist Card.
                </p>
                <Button asChild variant="outline">
                  <a href="#planes">Click para más información</a>
                </Button>
              </div>
              <div className="bg-neutral-50 rounded-xl p-6">
                <h3 className="text-lg font-semibold text-brand-blue-dark mb-4">Assist Card</h3>
                <div className="space-y-2 text-sm text-neutral-600">
                  <p><span className="font-medium">Validez máxima por viaje:</span> 30 días</p>
                  <p><span className="font-medium">Validez territorial:</span> Internacional</p>
                  <p><span className="font-medium">Limitaciones por edad:</span> N/A</p>
                </div>
                <p className="mt-4 text-xs text-neutral-500">
                  No te olvides de revisar y descargar las condiciones generales de tu asistencia.
                </p>
                <Button asChild variant="outline" size="sm" className="mt-4">
                  <a href="/downloads/condiciones-assist-card.pdf" download>Descargar Condiciones Generales</a>
                </Button>
              </div>
            </div>
          </div>

          {/* Bloque 3: Empresas */}
          <div className="bg-white rounded-2xl border border-neutral-200 p-8 md:p-12">
            <div className="grid md:grid-cols-2 gap-8 items-center">
              <div>
                <h2 className="text-2xl md:text-3xl text-brand-blue-dark mb-4">
                  Cuidamos la salud de tu empresa con excelencia médica
                </h2>
                <ul className="space-y-2 text-neutral-600">
                  <li className="flex items-center gap-2">
                    <svg aria-hidden="true" className="w-5 h-5 text-brand-green" fill="currentColor" viewBox="0 0 20 20"><path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" /></svg>
                    Atención preferencial para empresas
                  </li>
                  <li className="flex items-center gap-2">
                    <svg aria-hidden="true" className="w-5 h-5 text-brand-green" fill="currentColor" viewBox="0 0 20 20"><path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" /></svg>
                    Ofrecemos beneficios exclusivos para tus colaboradores
                  </li>
                  <li className="flex items-center gap-2">
                    <svg aria-hidden="true" className="w-5 h-5 text-brand-green" fill="currentColor" viewBox="0 0 20 20"><path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" /></svg>
                    Servicio integral de chequeos laborales
                  </li>
                </ul>
                <Button asChild variant="outline" className="mt-6">
                  <a href="/contacto">Contactar</a>
                </Button>
              </div>
              <div className="bg-neutral-100 rounded-xl h-48 flex items-center justify-center" aria-hidden="true">
                <span className="text-neutral-400">Imagen corporativa</span>
              </div>
            </div>
          </div>

          {/* Bloque 4: Instalaciones */}
          <div className="bg-white rounded-2xl border border-neutral-200 p-8 md:p-12">
            <div className="grid md:grid-cols-2 gap-8 items-center">
              <div className="bg-neutral-100 rounded-xl h-48 flex items-center justify-center" aria-hidden="true">
                <span className="text-neutral-400">Foto del Sanatorio</span>
              </div>
              <div>
                <h2 className="text-2xl md:text-3xl text-brand-blue-dark mb-4">
                  Nuestras instalaciones
                </h2>
                <p className="text-neutral-600 mb-6">
                  Nuestras instalaciones están diseñadas para ofrecer bienestar, tranquilidad y calidad. Conocé más beneficios de nuestro exclusivo Centro Médico.
                </p>
                <Button asChild variant="outline">
                  <a href="/contacto">Conocer más</a>
                </Button>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Beneficios Cooperativas */}
      <section className="py-16 px-6 bg-brand-green/10">
        <div className="max-w-4xl mx-auto text-center">
          <h2 className="text-2xl md:text-3xl text-brand-blue-dark mb-4">
            ¿Sos socio de la Cooperativa Universitaria o Cooperativa Mercado 4?
          </h2>
          <p className="text-neutral-600 mb-6">
            Accedé a beneficios exclusivos con tu plan de cobertura.
          </p>
          <div className="flex flex-wrap justify-center gap-4 mb-6">
            <span className="px-4 py-2 bg-white rounded-full text-sm text-brand-blue-dark font-medium border border-brand-blue/20">Plan SEVEN</span>
            <span className="px-4 py-2 bg-white rounded-full text-sm text-brand-blue-dark font-medium border border-brand-blue/20">Plan BETA</span>
          </div>
          <Button asChild className="bg-brand-blue hover:bg-brand-blue-dark">
            <a href="#planes">Verificá nuestros planes</a>
          </Button>
        </div>
      </section>

      {/* CTA Contact */}
      <section className="py-20 px-6">
        <div className="max-w-3xl mx-auto text-center">
          <h2 className="text-3xl text-brand-blue-dark">
            ¿Querés cotizar tu plan de salud?
          </h2>
          <p className="mt-3 text-neutral-600">
            Nuestro equipo te asesora sin compromiso. Te ayudamos a encontrar la mejor opción.
          </p>
          <div className="mt-8 flex flex-wrap justify-center gap-4">
<Button asChild size="lg" className="bg-brand-blue hover:bg-brand-blue-dark">
                <a href="#planes">Cotizar ahora</a>
              </Button>
            <Button asChild variant="outline" size="lg">
              <a href={WHATSAPP_URL} target="_blank" rel="noopener noreferrer">
                Escribinos por WhatsApp
              </a>
            </Button>
          </div>
        </div>
      </section>

      <Footer />
    </div>
  )
}