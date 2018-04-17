<?php

return array(
	//CLOUDAWS
	'events:calendar:today' => 'Hoy',
	'events:calendar:month' => 'Mes',
	'events:calendar:week' => 'Semana',
	'events:calendar:day' => 'Día',
	//\\

	'events' => 'Eventos',
	'events:calendar' => 'Calendario',
	'events:calendar:all' => 'Todos los calendarios del sitio',
	'events:calendar:mine' => 'Mis calendarios',
	'events:calendar:owner' => 'Calendarios de %s',
	'events:calendar:friends' => 'Calendarios de amigos',
	'events:calendar:group' => 'Calendarios de grupo',
	'events:calendar:none' => 'No hay calendarios para mostrar',
	'events:settings:sitecalendar:enable' => "¿Habilitar un calendario de sitio?",
	
	'events:new' => "Nuevo evento",
	'events:edit' => 'Editar evento',
	'events:add' => 'Nuevo evento',
	'events:full:view' => "Ver todos los detalles y comentarios del evento",
	
	// forms
	'events:edit:title:placeholder' => 'Título del evento',
	'events:edit:label:location' => 'Ubicación',
	'events:edit:label:start' => "Comienza",
	'events:edit:label:end' => "Finaliza",
	'events:edit:label:timezone' => 'Zona horaria',
	'events_ui:allday' => "Todo el día",
	'events_ui:description' => 'Descripción',
	'events_ui:byline' => 'Por %s',
	'events:status:recurring' => 'Evento recurrente',

	'events_ui:enable_reminders' => 'Habilitar recordatorios',
	'events_ui:reminders' => 'Recordatorios',
	'events_ui:reminders:add' => 'Añadir recordatorio',
	
	'events_ui:minutes' => 'minutos',
	'events_ui:hours' => 'horas',
	'events_ui:days' => 'dias',
	
	'events:feed:range' => 'Eventos entre %s y %s',
	'events:feed:month' => 'Próximos eventos',

	'events:view:calendar' => 'Vista calendario',
	'events:view:calendar:switch' => 'Cambiar a vista de calendario',
	'events:view:feed' => 'Vista listado',
	'events:view:feed:switch' => 'Cambiar a vista listado',

	'events:no_results' => 'No hay eventos para mostrar',

	'calendar:add' => 'Nuevo calendario',
	'events:calendar:add' => 'Crear un nuevo calendario',
	'events:calendar:edit' => 'Editar calendario',
	'events:calendar:groups:enable' => 'Habilitar calendario de grupo',
	'events:calendar:group' => 'Calendarios del grupo',
	'events:add_to_calendar:default' => 'Agregar a mi calendario',
	'events:remove_from_calendar:default' => 'Eliminar de mi calendario',
	'events:add_to_calendar:multi' => "Mostrar este evento en el (los) siguiente (s) calendario (s)",
	'events:calendars:addedremoved' => "Evento añadido a calendario (s) de %s y eliminado del calendario (s) de %s",
	'events:calendars:added' => "Evento añadido a calendario (s) de %s",
	'events:calendars:removed' => "Evento eliminado de calendario (s) de %s",
	'events:calendar:picker:title' => "Seleccione en qué calendarios debe estar el evento",
	'events:calendar:picker:help' => "Si está marcado, el evento se agregará al calendario; si no se selecciona, se eliminará del calendario.",
	'events:calendars:orphan:added' => "El evento huérfano se ha restaurado al calendario predeterminado",
	
	'river:event:create' => "%s ha creado el evento %s",
	'river:event:create:recurring' => "%s ha creado el evento recurrente %s",
	'river:comment:object:event' => "%s ha comentado en el evento %s",
	'events:start:time' => "Hora de inicio",
	'events:end:time' => "Hora de finalización",
	'events:error:empty_title' => "Debe introducir un título para el evento",
	'events_ui:resend:notifications' => "Reenviar notificaciones a miembros con este evento en sus calendarios ",
	'events_ui:default:calendar' => "CAlendario por defecto",
	'calendar:settings' => "Configuración de calendario",
	'calendar:groups:autosync' => "Sincronizar configuraciones de calendario de grupo",
	'calendar:groups:autosync:none' => "Aún no eres miembro de ningún grupo",
	'calendar:autosync' => "Sincronizar con su calendario predeterminado",
	'events:calendar:settings:saved' => "La configuración del calendario se ha guardado",
	'calendar:notifications' => "Notificaciones del calendario",
	'calendar:notifications:addtocal' => "Recibir notificaciones cuando los eventos se agregan a su calendario",
	'calendar:notifications:eventreminder' => "Recibir notificaciones de recordatorio antes de eventos",
	'calendar:notifications:eventupdate' => "Recibir notificaciones cuando los eventos se cambian/actualizan",
	'event:notify:addtocal:subject' => "Evento: %s%s por %s",
	'events:notify:subject:ingroup' => " en el grupo %s",
	'event:notify:addtocal:message' => "
%s ha añadido el evento %s en %s 

%s
%s
%s
",
	'event:notify:eventupdate:subject' => "Evento actualizado: %s%s por %s",
	'event:notify:eventupdate:message' => "
%s ha actualizado el evento %s en %s 

%s
%s
%s
",
	'event:notify:eventreminder:subject' => "Recordatorio de evento: %s%s comienza %s",
	'event:notify:eventreminder:message' => "
¡Un evento en tu calendario comienza pronto!

%s%s
%s
%s

%s
",

	'events_ui:cancel' => 'Eliminar',
	'events_ui:cancel:all' => 'Eliminar todas las ocurrencias de este evento',
	'events_ui:cancel:confirm' => '¿Seguro que quieres cancelar este evento? Esto no se puede deshacer',
	'events_ui:cancel:all:confirm' => '¿Seguro que quieres cancelar todos los eventos de esta serie? Esto no se puede deshacer',
	'events:settings:reminder:offsettime' => "Tiempo de compensación de recordatorio",
	'events:settings:reminder:offsettime:help' => "Introduzca un número de segundos para procesar los recordatorios de eventos antes de la programación. Esto debería compensar el tiempo de procesamiento, el retraso del correo electrónico y la ayuda para eventos populares que requieren mucho antelación en las notificaciones ",

	// widgets
	'events:widget:name' => "Eventos",
	'events:widget:description' => "Próximos eventos de sus calendarios",
	'events:widget:settings:numresults' => "Número de eventos a mostrar",
	'events:widgets:noresults' => "No hay eventos para listar",
	'events:widget:settings:upcoming' => "¿Limitar a eventos futuros?",

	// Timezones
	'events:settings:timezone' => 'Zona horaria',
	'events:settings:timezone:help' => 'Todas las marcas de tiempo se almacenan en UTC. Si deshabilita los selectores de zona horaria,
	por favor, especifique a qué entradas de la zona horaria deberían recurrir, si el usuario no tiene una zona horaria definida en sus configuraciones',
	'events:settings:timezone:picker' => 'Mostrar selector de zona horaria en formularios de eventos',
	'events:settings:timezone:default' => 'Default (fallback) timezone of the site',
	'events:settings:timezone:config' => 'Lista de zonas horarias para incluir en los selectores de zona horaria',
	'user:set:timezone' => "Configuraciones de zona horaria",
	'user:timezone:label' => "Su zona horaria",
	'user:timezone:success' => "La configuración de la zona horaria se ha actualizado.",
	'user:timezone:fail' => "La configuración de la zona horaria no se pudo guardar.",

	// iCal
	'events:settings:ical:help_page_url' => 'URL de la página de ayuda de iCal que se muestra en un formulario iCal',
	'events:view:ical' => 'iCal',
	'events:ical:feed' => 'Suscribirse al calendario a través de iCal',
	'events:ical:url' => 'URL fuente',
	'events:ical:help' => 'Las fuentes de iCal le permiten mantenerse al día con las actualizaciones de este calendario su calendario favorito, como Google Calendar. La siguiente URL es permanente: puede agregarla a su herramienta de calendario y compartirla con amigos. %s',
	'events:ical:learn_more' => 'Más información',

	// misc
	'admin:administer_utilities:events_migrate' => "Migrar eventos",
	'events:migrate:title' => "Migrar eventos desde plugin event_calendar",
	'events:migrate:count:none' => "¡Enhorabuena, no hay eventos event_calendar para migrar!",
	'events:migrate:run' => "Ejecutar la migración",
	'events:migrate:count' => "Hay %s event_calendar entidades que se pueden migrar",
	'events:migrate:system_message' => "La actualización de la migración se está ejecutando en segundo plano; si hay muchos eventos para migrar, puede llevar un tiempo. Vuelva a consultar más tarde para ver el progreso.",
	'events:migrate:inprogress' => "La migración aún está en progreso, falta %s para finalizar, por favor revise más tarde",
);
