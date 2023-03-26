
create table wikiproject_issue (
    id_issue        int not null references issue(id),
    id_project      int not null references wikiproject(id),
    primary key(id_issue, id_project)
);
-- to find the issues of a wikiproject
create index wikiproject_issue_id_issue_idx on wikiproject_issue(id_issue);
-- to find the wikiproject related to an issue
create index wikiproject_issue_id_project_idx on wikiproject_issue(id_project);
