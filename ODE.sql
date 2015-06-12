--
-- PostgreSQL database dump
--

-- Dumped from database version 9.4.2
-- Dumped by pg_dump version 9.4.0
-- Started on 2015-06-11 16:21:07 CEST

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

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
    transparent integer
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
    NO MINVALUE
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
    component text
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

COPY calendar (uid, principaluri, displayname, uri, synctoken, description, calendarorder, calendarcolor, timezone, components, transparent) FROM stdin;
3	principals/admin	Concert 2015/2017	concert-2015-2017	23	Calendrier des concerts de la saison 2015/2016	1	#882F00FF	BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Apple Inc.//Mac OS X 10.9.5//EN\r\nCALSCALE:GREGORIAN\r\nBEGIN:VTIMEZONE\r\nTZID:Europe/Paris\r\nBEGIN:DAYLIGHT\r\nTZOFFSETFROM:+0100\r\nRRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU\r\nDTSTART:19810329T020000\r\nTZNAME:UTC+2\r\nTZOFFSETTO:+0200\r\nEND:DAYLIGHT\r\nBEGIN:STANDARD\r\nTZOFFSETFROM:+0200\r\nRRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU\r\nDTSTART:19961027T030000\r\nTZNAME:UTC+1\r\nTZOFFSETTO:+0100\r\nEND:STANDARD\r\nEND:VTIMEZONE\r\nEND:VCALENDAR\r\n	{VEVENT,VTODO}	0
14	principals/test	Calendrier Test	calendrier-test	7	Calendrier de test	1	#711A76FF	BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Apple Inc.//Mac OS X 10.9.5//EN\r\nCALSCALE:GREGORIAN\r\nBEGIN:VTIMEZONE\r\nTZID:Europe/Paris\r\nBEGIN:DAYLIGHT\r\nTZOFFSETFROM:+0100\r\nRRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU\r\nDTSTART:19810329T020000\r\nTZNAME:UTC+2\r\nTZOFFSETTO:+0200\r\nEND:DAYLIGHT\r\nBEGIN:STANDARD\r\nTZOFFSETFROM:+0200\r\nRRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU\r\nDTSTART:19961027T030000\r\nTZNAME:UTC+1\r\nTZOFFSETTO:+0100\r\nEND:STANDARD\r\nEND:VTIMEZONE\r\nEND:VCALENDAR\r\n	{VEVENT,VTODO}	0
5	principals/admin	Sans titre1337	BB3B2036-9091-443C-80F1-32B023D5D1EE	33	\N	2	#0E61B9FF	BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Apple Inc.//Mac OS X 10.9.5//EN\r\nCALSCALE:GREGORIAN\r\nBEGIN:VTIMEZONE\r\nTZID:Europe/Paris\r\nBEGIN:DAYLIGHT\r\nTZOFFSETFROM:+0100\r\nRRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU\r\nDTSTART:19810329T020000\r\nTZNAME:UTC+2\r\nTZOFFSETTO:+0200\r\nEND:DAYLIGHT\r\nBEGIN:STANDARD\r\nTZOFFSETFROM:+0200\r\nRRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU\r\nDTSTART:19961027T030000\r\nTZNAME:UTC+1\r\nTZOFFSETTO:+0100\r\nEND:STANDARD\r\nEND:VTIMEZONE\r\nEND:VCALENDAR\r\n	{VEVENT}	0
\.


--
-- TOC entry 2307 (class 0 OID 16424)
-- Dependencies: 176
-- Data for Name: calendarchange; Type: TABLE DATA; Schema: public; Owner: ODE
--

COPY calendarchange (id, uri, synctoken, calendarid, operation) FROM stdin;
1		1	5	2
4	59F676E4-4A93-40D2-A18D-80939975645E.ics	1	5	1
5	A5C45656-71F6-4AD0-BC30-4E8C02185551.ics	1	5	1
6	1A8A312A-0053-45A7-9148-A73D39F4BB1D.ics	2	5	1
7	1A8A312A-0053-45A7-9148-A73D39F4BB1D.ics	3	5	2
8		1	3	2
9		4	5	2
10	1A8A312A-0053-45A7-9148-A73D39F4BB1D.ics	5	5	2
11		2	3	2
12		6	5	2
13	1A8A312A-0053-45A7-9148-A73D39F4BB1D.ics	7	5	2
14	1A8A312A-0053-45A7-9148-A73D39F4BB1D.ics	8	5	3
15	60B30E9C-582A-4A01-B8DA-B5F5BC4167A9.ics.ics	3	3	3
16	60B30E9C-582A-4A01-B8DA-B5F5BC4167A9.ics.ics	4	3	3
17	60B30E9C-582A-4A01-B8DA-B5F5BC4167A9.ics	5	3	3
18	C9273F14-03E1-48CF-B197-CAAC87F61751.ics	6	3	2
19	A5C45656-71F6-4AD0-BC30-4E8C02185551.ics	9	5	2
20	A5C45656-71F6-4AD0-BC30-4E8C02185551.ics	10	5	2
21		7	3	2
22		11	5	2
23	A5C45656-71F6-4AD0-BC30-4E8C02185551.ics	12	5	2
24	A5C45656-71F6-4AD0-BC30-4E8C02185551.ics	13	5	2
25		14	5	2
26		8	3	2
27	A5C45656-71F6-4AD0-BC30-4E8C02185551.ics	15	5	2
28	A5C45656-71F6-4AD0-BC30-4E8C02185551.ics	16	5	2
29	A5C45656-71F6-4AD0-BC30-4E8C02185551.ics	17	5	2
30	A5C45656-71F6-4AD0-BC30-4E8C02185551.ics	18	5	3
31	C9531A50-43E7-4CDB-9551-265B56ACA303.ics	19	5	1
32	C9531A50-43E7-4CDB-9551-265B56ACA303.ics	20	5	2
33	C9531A50-43E7-4CDB-9551-265B56ACA303.ics	21	5	2
34	C9273F14-03E1-48CF-B197-CAAC87F61751.ics	9	3	3
35	C9531A50-43E7-4CDB-9551-265B56ACA303.ics	22	5	3
36	F3FA5682-8FE2-4A0C-AAC2-9FBA1CD796E9.ics	23	5	1
37	00E8BC06-9616-4E95-9DF6-94659D749874.ics	24	5	1
38	F3FA5682-8FE2-4A0C-AAC2-9FBA1CD796E9.ics	25	5	3
39		26	5	2
40		10	3	2
41	C0A0A1E2-246F-4465-9215-4C50363CE702.ics	27	5	1
42	94A39AF5-8225-460D-AF9B-9C6578D4784C.ics	28	5	1
43	94A39AF5-8225-460D-AF9B-9C6578D4784C.ics	29	5	3
44	00E8BC06-9616-4E95-9DF6-94659D749874.ics	30	5	2
45	6DA7F8CE-D512-4959-B06F-66B212428782.ics	1	14	1
46		2	14	2
47		3	14	2
48		4	14	2
49		5	14	2
50	6DA7F8CE-D512-4959-B06F-66B212428782.ics	6	14	3
51	00E8BC06-9616-4E95-9DF6-94659D749874.ics	31	5	3
52	C0A0A1E2-246F-4465-9215-4C50363CE702.ics	32	5	3
53	EA776249-F290-416A-80D1-828BE0DCF726.ics	11	3	1
54	EA776249-F290-416A-80D1-828BE0DCF726.ics	12	3	2
55	EA776249-F290-416A-80D1-828BE0DCF726.ics	13	3	2
56	E8364EC9-64DE-42CE-9F45-1647580ABF66.ics	14	3	1
57	EA776249-F290-416A-80D1-828BE0DCF726.ics	15	3	2
58	EA776249-F290-416A-80D1-828BE0DCF726.ics	16	3	3
59	234C32BF-9F13-458B-9354-8C64E3E0EB7D.ics	17	3	1
60	234C32BF-9F13-458B-9354-8C64E3E0EB7D.ics	18	3	2
61	234C32BF-9F13-458B-9354-8C64E3E0EB7D.ics	19	3	2
62	234C32BF-9F13-458B-9354-8C64E3E0EB7D.ics	20	3	2
63	234C32BF-9F13-458B-9354-8C64E3E0EB7D.ics	21	3	2
64	234C32BF-9F13-458B-9354-8C64E3E0EB7D.ics	22	3	2
\.


--
-- TOC entry 2324 (class 0 OID 0)
-- Dependencies: 175
-- Name: calendarchanges_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ODE
--

SELECT pg_catalog.setval('calendarchanges_id_seq', 64, true);


--
-- TOC entry 2305 (class 0 OID 16407)
-- Dependencies: 174
-- Data for Name: calendarobject; Type: TABLE DATA; Schema: public; Owner: ODE
--

COPY calendarobject (uri, lastmodified, calendarid, calendardata, etag, size, extracted_data, uid, component) FROM stdin;
E8364EC9-64DE-42CE-9F45-1647580ABF66.ics	1433951865	3	BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Sabre//Sabre VObject 3.4.3//EN\r\nCALSCALE:GREGORIAN\r\nBEGIN:VEVENT\r\nUID:E8364EC9-64DE-42CE-9F45-1647580ABF66\r\nDTSTAMP:20150610T175745Z\r\nSUMMARY:LALALA\r\nDESCRIPTION:zeiufbze\r\nX-ODE-CATEGORY:zreoguner\r\nDTSTART;TZID=Europe/Berlin:20150610T175400\r\nDTEND;TZID=Europe/Berlin:20150610T175400\r\nSTATUS:1\r\nURL;VALUE=URI:projet-ode.fr/event/E8364EC9-64DE-42CE-9F45-1647580ABF66\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n	11540150a261f28f9846521cff2d26bb	433	{"name":"LALALA","id":"E8364EC9-64DE-42CE-9F45-1647580ABF66","description":"zeiufbze","category":"zreoguner","date_start":"20150610T175400","date_end":"20150610T175400","status":"1","url":"projet-ode.fr\\/event\\/E8364EC9-64DE-42CE-9F45-1647580ABF66"}	E8364EC9-64DE-42CE-9F45-1647580ABF66	vevent
234C32BF-9F13-458B-9354-8C64E3E0EB7D.ics	1434028640	3	BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Sabre//Sabre VObject 3.4.3//EN\r\nCALSCALE:GREGORIAN\r\nBEGIN:VEVENT\r\nUID:234C32BF-9F13-458B-9354-8C64E3E0EB7D\r\nDTSTAMP:20150611T151720Z\r\nSUMMARY:Concert de Shakaponk\r\nDESCRIPTION:Tournée pour la sortie de leurs derniers albums: Black Pixel A\r\n pe\r\nX-ODE-CATEGORY:Musique\r\nX-ODE-TAGS:Concert\\; Musique\\; Rock\\; ShakaPonk\\;\r\nDTSTART;TZID=Europe/Berlin:20150613T203000\r\nDTEND;TZID=Europe/Berlin:20150614T000500\r\nLOCATION:Stéréolux\\, Nantes\\, France\r\nX-ODE-LOCATION-PRECISION:Salle de Concert n°3\r\nX-ODE-LOCATION-CAPACITY:3500\r\nX-ODE-ATTENDEES:Shakaponk\\; Lisa Leblanc\r\nX-ODE-DURATION:2h00 à 3h00\r\nSTATUS:CONFIRMED\r\nX-ODE-PROMOTER:Association Stéréolux\r\nURL:projet-ode.fr/event/234C32BF-9F13-458B-9354-8C64E3E0EB7D\r\nX-ODE-URL-PROMOTER:http://www.stereolux.org\r\nX-ODE-URLS-MEDIAS:https://youtu.be/aRljVackZ08,https://soundcloud.com/shaka\r\n ponkofficial/sets/the-black-pixel-ape,https://flic.kr/p/bxmon3\r\nX-ODE-CONTACT-NAME:John Smith\r\nX-ODE-CONTACT-EMAIL:john.smith@stereolux.org\r\nX-ODE-PRICE-STANDARD:25\r\nX-ODE-PRICE-REDUCED:20\r\nX-ODE-PRICE-CHILDREN:15\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n	61c96726578184141a996cbb866949ff	1121	{"name":"Concert de Shakaponk","id":"234C32BF-9F13-458B-9354-8C64E3E0EB7D","description":"Tourn\\u00e9e pour la sortie de leurs derniers albums: Black Pixel Ape","category":"Musique","tags":"Concert; Musique; Rock; ShakaPonk;","date_start":"20150613T203000","date_end":"20150614T000500","location_name":"St\\u00e9r\\u00e9olux, Nantes, France","location_precision":"Salle de Concert n\\u00b03","location_capacity":"3500","attendees":"Shakaponk; Lisa Leblanc","duration":"2h00 \\u00e0 3h00","status":"CONFIRMED","promoter":"Association St\\u00e9r\\u00e9olux","url":"projet-ode.fr\\/event\\/234C32BF-9F13-458B-9354-8C64E3E0EB7D","url_promoter":"http:\\/\\/www.stereolux.org","urls_medias":["https:\\/\\/youtu.be\\/aRljVackZ08","https:\\/\\/soundcloud.com\\/shakaponkofficial\\/sets\\/the-black-pixel-ape","https:\\/\\/flic.kr\\/p\\/bxmon3"],"contact_name":"John Smith","contact_email":"john.smith@stereolux.org","price_standard":"25","price_reduced":"20","price_children":"15"}	234C32BF-9F13-458B-9354-8C64E3E0EB7D	vevent
\.


--
-- TOC entry 2325 (class 0 OID 0)
-- Dependencies: 172
-- Name: calendars_uid_seq; Type: SEQUENCE SET; Schema: public; Owner: ODE
--

SELECT pg_catalog.setval('calendars_uid_seq', 14, true);


--
-- TOC entry 2311 (class 0 OID 16461)
-- Dependencies: 180
-- Data for Name: principal; Type: TABLE DATA; Schema: public; Owner: ODE
--

COPY principal (id, uri, email, displayname, vcardurl) FROM stdin;
1	principals/admin	admin@admin.fr	Admin	\N
2	principals/admin/calendar-proxy-read	admin@admin.fr	Admin	\N
3	principals/admin/calendar-proxy-write	admin@admin.fr	Admin	\N
4	principals/test	test@test.com	Test	\N
5	principals/test/calendar-proxy-read	test@test.com	Test	\N
6	principals/test/calendar-proxy-write	test@test.com	Test	\N
\.


--
-- TOC entry 2326 (class 0 OID 0)
-- Dependencies: 179
-- Name: principal_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ODE
--

SELECT pg_catalog.setval('principal_id_seq', 6, true);


--
-- TOC entry 2327 (class 0 OID 0)
-- Dependencies: 177
-- Name: user_uid_seq; Type: SEQUENCE SET; Schema: public; Owner: ODE
--

SELECT pg_catalog.setval('user_uid_seq', 4, true);


--
-- TOC entry 2309 (class 0 OID 16446)
-- Dependencies: 178
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: ODE
--

COPY users (id, username, username_canonical, email, email_canonical, salt, password, password_digesta, locked, expired, expires_at, confirmation_token, password_requested_at, roles, credentials_expired, credentials_expire_at, enabled, last_login) FROM stdin;
4	Test	test	test@test.com	test@test.com	yioeojaymlwcwkk40s0w4gc4g0o4c8	84VYll13DEs5cy9kSEBVRyP3YrtMlbTTtZ2n2olBZp7sapMul9X4jLYB0LCT8skVfYMiRVptUpt5JgCDNt0hWQ==	3e3e791c793f40a22335716a5610bffb	f	f	\N	\N	\N	{}	f	\N	t	\N
2	admin	admin	admin@admin.fr	admin@admin.fr	buk14ljud3scw4k80k8kcckks8gc8o0	mnvGN+JXg8jkCr+9AZg7fCP8BEbgGpoclogxp76h7kHg1wAstu8JwZmABhRQYHSUl+KpN4MJIAwXfYoLsMn7MQ==	87fd274b7b6c01e48d7c2f965da8ddf7	f	f	\N	\N	\N	\N	f	\N	t	2015-06-10 17:32:30
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


-- Completed on 2015-06-11 16:21:07 CEST

--
-- PostgreSQL database dump complete
--
