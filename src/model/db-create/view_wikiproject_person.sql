
-- View used by postgrest api
create or replace view view_wikiproject_person as
select
    p.id        "person_id",
    p.slug      "person_slug",
    p.name      "person_name",
    p.birth     "person_birth",
    wp.id       "project_id",
    wp.slug     "project_slug"
from
    person              p,
    wikiproject         wp,
    wikiproject_person  wp_p
where  p.id = wp_p.id_person
  and wp.id = wp_p.id_project
;
