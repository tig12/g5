

create or replace view api_persongroop as
select
    p.slug              "person_slug",
    p.ids_in_sources,
    p.sex,
    p.name              "person_name",
    p.occus,
    p.birth,
    g.name              "group_name",
    g.slug              "group_slug"
from
    person p,
    groop g,
    person_groop pg
where p.id = pg.id_person
  and g.id = pg.id_groop
;
