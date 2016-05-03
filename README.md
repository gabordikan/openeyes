# PASAPI

## Overview

This module is designed to provide a simplified API for managing patients in OpenEyes from an external source, specifically a PAS.

At the time of development, a core API does exist in the OpenEyes core, but it is based on an out of date FHIR specification, and early testing indicated some issues that would need updating to resolve this. Furthermore, by developing a specific API for PAS integration, we are able to manage the external references cleanly, and reduce the number of calls required to add or update a patient.

## Design

The basic design is to support a PUT call to openeyes with the details of a patient. If the patient doesn't exist, it will be created. If it does already exist, then the details of the patient will be updated. Within this, the patient GP and Practice relations will be defined using the standard external identifiers that have been imported on those records.

The external identifier is used for defining a patient instance, and is tracked internally in the module via the PasApiAssignment model.

## Configuration

The usual configuration for the module must be added to the modules array:

    'PASAPI' => array( 'class' => '\OEModule\PASAPI\PASAPIModule', )
 
### Patient Addresses
 
 Patient addresses have no external identifier for tracking their changes. As a result, the system verifies that an address is the same as a previously provided address by comparing postcodes. If there is a postcode match, then an address will be updated, rather than a new Address instance created.
 
## Example(s)
 
 The URL pattern for the PUT call is as follows:
 
    http://[oe-base-url]/PASAPI/V1/Patient/[external-id]
    
The XML for defining a patient is as follows.

 
    <Patient>
        <NHSNumber>0123456789</NHSNumber>
        <HospitalNumber>92312423</HospitalNumber>
        <Title>MRS</Title>
        <FirstName>Violet</FirstName>
        <Surname>Coffin</Surname>
        <DateOfBirth>1978-03-01</DateOfBirth>
        <Gender>F</Gender>
        <AddressList>
            <Address>
                <Line1>82 Scarisbrick Lane</Line1>
                <Line2/>
                <City>Bethersden</City>
                <County>West Yorkshire</County>
                <Postcode>QA88 2GC</Postcode>
                <Country>GB</Country>
                <Type>HOME</Type>
            </Address>
        </AddressList>
        <TelephoneNumber>03040 6024378</TelephoneNumber>
        <EthnicGroup>A</EthnicGroup>
        <DateOfDeath/>
        <PracticeCode>F001</PracticeCode>
        <GpCode>G0102926</GpCode>
    </Patient>
    
The response XML is as follows:

    <Success>
        <Message>Patient created</Message>
        <Id>[internal patient id]</Id>
    </Success>
    
The following elements will generate warnings if the given values do not match valid values in the system:
 
* Gender
* EthnicGroup
* PracticeCode
* GpCode

e.g.

    <Success>
        <Message>Patient created</Message>
        <Id>[internal patient id]</Id>
        <Warnings>
            <Warning>Unrecognised Gender X</Warning>
        </Warnings>
    </Success>
 
### Update only
 
If the intention is for the patient to only be updated, and not created if it doesn't exist, the updateOnly attribute should be used:

    <Patient updateOnly="1">
         <NHSNumber>0123456789</NHSNumber>
         ...
         
## Remapping values

To deal with the issue of external sources not mapping to internal resource values within OpenEyes, a remapping of values can be configured through the admin.

Each XpathRemap object is defined as having an xpath and a name attribute (which is simply a descriptive label). This has zero or more RemapValue attributes that will swap any matching input values for a different output value.

These can be managed through the module admin screen.

(note that whilst in the admin one can only provide an empty string, the code supports a null value which will lead the entire node to be removed, rather than emptied).

For example, if the code AX is coming through from the message sender for ethnic group:

1. Create an XpathRemap object with Xpath /Patient/EthnicGroup and name "Ethnic Group Remapping".
1. Click on the value count (which will be zero initially), and add an entry with input AX, and output A.

## Running tests

The tests will currently only run within the context of the wider OpenEyes implementation, and relies on the Rest Test Case in core (which in turn relies on guzzle - this may not be present if you haven't got the right composer setup in place). 

__NB__ You need to be using the shell wrapper to phpunit to ensure everything is bootstrapped appropriately.
