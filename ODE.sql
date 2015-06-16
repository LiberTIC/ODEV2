--
-- PostgreSQL database dump
--

-- Dumped from database version 9.4.2
-- Dumped by pg_dump version 9.4.0
-- Started on 2015-06-16 11:56:01 CEST

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

DROP DATABASE "ODE";
--
-- TOC entry 2316 (class 1262 OID 16393)
-- Name: ODE; Type: DATABASE; Schema: -; Owner: ODE
--

CREATE DATABASE "ODE" WITH TEMPLATE = template0 ENCODING = 'UTF8' LC_COLLATE = 'en_US.UTF-8' LC_CTYPE = 'en_US.UTF-8';


ALTER DATABASE "ODE" OWNER TO "ODE";

\connect "ODE"

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- TOC entry 6 (class 2615 OID 2200)
-- Name: public; Type: SCHEMA; Schema: -; Owner: ODE
--

CREATE SCHEMA public;


ALTER SCHEMA public OWNER TO "ODE";

--
-- TOC entry 2317 (class 0 OID 0)
-- Dependencies: 6
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: ODE
--

COMMENT ON SCHEMA public IS 'standard public schema';


--
-- TOC entry 181 (class 3079 OID 12123)
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- TOC entry 2319 (class 0 OID 0)
-- Dependencies: 181
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- TOC entry 173 (class 1259 OID 16396)
-- Name: calendar; Type: TABLE; Schema: public; Owner: ODE; Tablespace: 
--

CREATE TABLE calendar (
    uid integer NOT NULL,
    principaluri text,
    displayname text,
    uri text,
    synctoken integer,
    description text,
    calendarorder integer,
    calendarcolor text,
    timezone text,
    components text[],
    transparent integer,
    slug text
);


ALTER TABLE calendar OWNER TO "ODE";

--
-- TOC entry 176 (class 1259 OID 16424)
-- Name: calendarchange; Type: TABLE; Schema: public; Owner: ODE; Tablespace: 
--

CREATE TABLE calendarchange (
    id integer NOT NULL,
    uri text,
    synctoken integer,
    calendarid integer,
    operation integer
);


ALTER TABLE calendarchange OWNER TO "ODE";

--
-- TOC entry 175 (class 1259 OID 16422)
-- Name: calendarchanges_id_seq; Type: SEQUENCE; Schema: public; Owner: ODE
--

CREATE SEQUENCE calendarchanges_id_seq
    START WITH 1
    INCREMENT BY 1
    MINVALUE 0
    NO MAXVALUE
    CACHE 1;


ALTER TABLE calendarchanges_id_seq OWNER TO "ODE";

--
-- TOC entry 2320 (class 0 OID 0)
-- Dependencies: 175
-- Name: calendarchanges_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ODE
--

ALTER SEQUENCE calendarchanges_id_seq OWNED BY calendarchange.id;


--
-- TOC entry 174 (class 1259 OID 16407)
-- Name: calendarobject; Type: TABLE; Schema: public; Owner: ODE; Tablespace: 
--

CREATE TABLE calendarobject (
    uri text,
    lastmodified integer,
    calendarid integer,
    calendardata text,
    etag text,
    size integer,
    extracted_data json,
    uid text NOT NULL,
    component text,
    slug text
);


ALTER TABLE calendarobject OWNER TO "ODE";

--
-- TOC entry 172 (class 1259 OID 16394)
-- Name: calendars_uid_seq; Type: SEQUENCE; Schema: public; Owner: ODE
--

CREATE SEQUENCE calendars_uid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE calendars_uid_seq OWNER TO "ODE";

--
-- TOC entry 2321 (class 0 OID 0)
-- Dependencies: 172
-- Name: calendars_uid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ODE
--

ALTER SEQUENCE calendars_uid_seq OWNED BY calendar.uid;


--
-- TOC entry 180 (class 1259 OID 16461)
-- Name: principal; Type: TABLE; Schema: public; Owner: ODE; Tablespace: 
--

CREATE TABLE principal (
    id integer NOT NULL,
    uri text,
    email text,
    displayname text,
    vcardurl text
);


ALTER TABLE principal OWNER TO "ODE";

--
-- TOC entry 179 (class 1259 OID 16459)
-- Name: principal_id_seq; Type: SEQUENCE; Schema: public; Owner: ODE
--

CREATE SEQUENCE principal_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE principal_id_seq OWNER TO "ODE";

--
-- TOC entry 2322 (class 0 OID 0)
-- Dependencies: 179
-- Name: principal_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ODE
--

ALTER SEQUENCE principal_id_seq OWNED BY principal.id;


--
-- TOC entry 178 (class 1259 OID 16446)
-- Name: users; Type: TABLE; Schema: public; Owner: ODE; Tablespace: 
--

CREATE TABLE users (
    id integer NOT NULL,
    username text,
    username_canonical text,
    email text,
    email_canonical text,
    salt text,
    password text,
    password_digesta text,
    locked boolean DEFAULT false,
    expired boolean DEFAULT false,
    expires_at text,
    confirmation_token text,
    password_requested_at text,
    roles text[],
    credentials_expired boolean DEFAULT false,
    credentials_expire_at text,
    enabled boolean DEFAULT true,
    last_login timestamp without time zone
);


ALTER TABLE users OWNER TO "ODE";

--
-- TOC entry 177 (class 1259 OID 16444)
-- Name: user_uid_seq; Type: SEQUENCE; Schema: public; Owner: ODE
--

CREATE SEQUENCE user_uid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE user_uid_seq OWNER TO "ODE";

--
-- TOC entry 2323 (class 0 OID 0)
-- Dependencies: 177
-- Name: user_uid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ODE
--

ALTER SEQUENCE user_uid_seq OWNED BY users.id;


--
-- TOC entry 2174 (class 2604 OID 16399)
-- Name: uid; Type: DEFAULT; Schema: public; Owner: ODE
--

ALTER TABLE ONLY calendar ALTER COLUMN uid SET DEFAULT nextval('calendars_uid_seq'::regclass);


--
-- TOC entry 2175 (class 2604 OID 16427)
-- Name: id; Type: DEFAULT; Schema: public; Owner: ODE
--

ALTER TABLE ONLY calendarchange ALTER COLUMN id SET DEFAULT nextval('calendarchanges_id_seq'::regclass);


--
-- TOC entry 2181 (class 2604 OID 16464)
-- Name: id; Type: DEFAULT; Schema: public; Owner: ODE
--

ALTER TABLE ONLY principal ALTER COLUMN id SET DEFAULT nextval('principal_id_seq'::regclass);


--
-- TOC entry 2176 (class 2604 OID 16449)
-- Name: id; Type: DEFAULT; Schema: public; Owner: ODE
--

ALTER TABLE ONLY users ALTER COLUMN id SET DEFAULT nextval('user_uid_seq'::regclass);


--
-- TOC entry 2304 (class 0 OID 16396)
-- Dependencies: 173
-- Data for Name: calendar; Type: TABLE DATA; Schema: public; Owner: ODE
--

COPY calendar (uid, principaluri, displayname, uri, synctoken, description, calendarorder, calendarcolor, timezone, components, transparent, slug) FROM stdin;
1	principals/admin	Calendrier Test	6404AFAC-6578-4A8B-B8E5-1C916BEB8113	1	Calendrier de test	\N	\N	\N	{VEVENT,VTODO}	0	calendrier-test
\.


--
-- TOC entry 2307 (class 0 OID 16424)
-- Dependencies: 176
-- Data for Name: calendarchange; Type: TABLE DATA; Schema: public; Owner: ODE
--

COPY calendarchange (id, uri, synctoken, calendarid, operation) FROM stdin;
\.


--
-- TOC entry 2324 (class 0 OID 0)
-- Dependencies: 175
-- Name: calendarchanges_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ODE
--

SELECT pg_catalog.setval('calendarchanges_id_seq', 0, true);


--
-- TOC entry 2305 (class 0 OID 16407)
-- Dependencies: 174
-- Data for Name: calendarobject; Type: TABLE DATA; Schema: public; Owner: ODE
--

COPY calendarobject (uri, lastmodified, calendarid, calendardata, etag, size, extracted_data, uid, component, slug) FROM stdin;
\.


--
-- TOC entry 2325 (class 0 OID 0)
-- Dependencies: 172
-- Name: calendars_uid_seq; Type: SEQUENCE SET; Schema: public; Owner: ODE
--

SELECT pg_catalog.setval('calendars_uid_seq', 1, true);


--
-- TOC entry 2311 (class 0 OID 16461)
-- Dependencies: 180
-- Data for Name: principal; Type: TABLE DATA; Schema: public; Owner: ODE
--

COPY principal (id, uri, email, displayname, vcardurl) FROM stdin;
1	principals/admin	admin@admin.fr	Admin	\N
2	principals/admin/calendar-proxy-read	admin@admin.fr	Admin	\N
3	principals/admin/calendar-proxy-write	admin@admin.fr	Admin	\N
\.


--
-- TOC entry 2326 (class 0 OID 0)
-- Dependencies: 179
-- Name: principal_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ODE
--

SELECT pg_catalog.setval('principal_id_seq', 3, true);


--
-- TOC entry 2327 (class 0 OID 0)
-- Dependencies: 177
-- Name: user_uid_seq; Type: SEQUENCE SET; Schema: public; Owner: ODE
--

SELECT pg_catalog.setval('user_uid_seq', 1, true);


--
-- TOC entry 2309 (class 0 OID 16446)
-- Dependencies: 178
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: ODE
--

COPY users (id, username, username_canonical, email, email_canonical, salt, password, password_digesta, locked, expired, expires_at, confirmation_token, password_requested_at, roles, credentials_expired, credentials_expire_at, enabled, last_login) FROM stdin;
1	admin	admin	admin@admin.fr	admin@admin.fr	buk14ljud3scw4k80k8kcckks8gc8o0	mnvGN+JXg8jkCr+9AZg7fCP8BEbgGpoclogxp76h7kHg1wAstu8JwZmABhRQYHSUl+KpN4MJIAwXfYoLsMn7MQ==	87fd274b7b6c01e48d7c2f965da8ddf7	f	f	\N	\N	\N	\N	f	\N	t	2015-06-16 11:52:51
\.


--
-- TOC entry 2183 (class 2606 OID 16404)
-- Name: calendar_pkey; Type: CONSTRAINT; Schema: public; Owner: ODE; Tablespace: 
--

ALTER TABLE ONLY calendar
    ADD CONSTRAINT calendar_pkey PRIMARY KEY (uid);


--
-- TOC entry 2188 (class 2606 OID 16432)
-- Name: calendarchanges_pkey; Type: CONSTRAINT; Schema: public; Owner: ODE; Tablespace: 
--

ALTER TABLE ONLY calendarchange
    ADD CONSTRAINT calendarchanges_pkey PRIMARY KEY (id);


--
-- TOC entry 2185 (class 2606 OID 16415)
-- Name: calendarobject_pkey; Type: CONSTRAINT; Schema: public; Owner: ODE; Tablespace: 
--

ALTER TABLE ONLY calendarobject
    ADD CONSTRAINT calendarobject_pkey PRIMARY KEY (uid);


--
-- TOC entry 2191 (class 2606 OID 16455)
-- Name: user_pkey; Type: CONSTRAINT; Schema: public; Owner: ODE; Tablespace: 
--

ALTER TABLE ONLY users
    ADD CONSTRAINT user_pkey PRIMARY KEY (id);


--
-- TOC entry 2189 (class 1259 OID 16443)
-- Name: fki_cal_fkey; Type: INDEX; Schema: public; Owner: ODE; Tablespace: 
--

CREATE INDEX fki_cal_fkey ON calendarchange USING btree (calendarid);


--
-- TOC entry 2186 (class 1259 OID 16421)
-- Name: fki_calendar_fkey; Type: INDEX; Schema: public; Owner: ODE; Tablespace: 
--

CREATE INDEX fki_calendar_fkey ON calendarobject USING btree (calendarid);


--
-- TOC entry 2193 (class 2606 OID 16438)
-- Name: cal_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ODE
--

ALTER TABLE ONLY calendarchange
    ADD CONSTRAINT cal_fkey FOREIGN KEY (calendarid) REFERENCES calendar(uid);


--
-- TOC entry 2192 (class 2606 OID 16416)
-- Name: calendar_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ODE
--

ALTER TABLE ONLY calendarobject
    ADD CONSTRAINT calendar_fkey FOREIGN KEY (calendarid) REFERENCES calendar(uid);


--
-- TOC entry 2318 (class 0 OID 0)
-- Dependencies: 6
-- Name: public; Type: ACL; Schema: -; Owner: ODE
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM "ODE";
GRANT ALL ON SCHEMA public TO "ODE";
GRANT ALL ON SCHEMA public TO PUBLIC;


-- Completed on 2015-06-16 11:56:01 CEST

--
-- PostgreSQL database dump complete
--

