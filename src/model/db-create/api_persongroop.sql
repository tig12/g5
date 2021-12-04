
-- View used by postgrest api

create or replace view api_persongroop as
select
    p.slug              "person_slug",
    g.slug              "group_slug",
    p.ids_in_sources,
    p.ids_partial,
    p.sex,
    p.name,
    p.occus,
    p.birth
from
    person p,
    groop g,
    person_groop pg
where p.id = pg.id_person
  and g.id = pg.id_groop
;
